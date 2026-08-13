<?php

declare(strict_types=1);

use App\Domain\Models\OrganizationPeriod;
use App\Domain\Models\User;
use Database\Seeders\OrganizationPositionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(OrganizationPositionSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    // editor has 'organization-periods.view' but NOT create/update/delete
    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

// ─── Authentication ───────────────────────────────────────────────────────────

it('rejects unauthenticated list requests', function (): void {
    $this->getJson('/api/v1/admin/organization/periods')
        ->assertStatus(401);
});

it('rejects unauthenticated create requests', function (): void {
    $this->postJson('/api/v1/admin/organization/periods', [
        'label' => 'Periode 2026-2028',
        'startDate' => '2026-01-01',
        'endDate' => '2028-12-31',
    ])->assertStatus(401);
});

// ─── Authorization ────────────────────────────────────────────────────────────

it('allows any authenticated user to list periods (view permission)', function (): void {
    $this->actingAs($this->editor)
        ->getJson('/api/v1/admin/organization/periods')
        ->assertOk();
});

it('rejects editor creating a period (write-restricted)', function (): void {
    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/organization/periods', [
            'label' => 'Should Fail',
            'startDate' => '2026-01-01',
            'endDate' => '2028-12-31',
        ])->assertStatus(403);
});

it('rejects editor deleting a period', function (): void {
    $period = OrganizationPeriod::factory()->create();

    $this->actingAs($this->editor)
        ->deleteJson("/api/v1/admin/organization/periods/{$period->id}")
        ->assertStatus(403);
});

it('rejects editor activating a period', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);

    $this->actingAs($this->editor)
        ->postJson("/api/v1/admin/organization/periods/{$period->id}/activate")
        ->assertStatus(403);
});

// ─── List ─────────────────────────────────────────────────────────────────────

it('lists periods with pagination envelope', function (): void {
    OrganizationPeriod::factory()->count(3)->create(['is_active' => false]);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/periods?perPage=2')
        ->assertOk();

    $response->assertJsonPath('success', true)
        ->assertJsonPath('meta.pagination.strategy', 'offset')
        ->assertJsonPath('meta.pagination.perPage', 2);
});

it('returns empty data array when no periods exist after seeded period is deleted', function (): void {
    OrganizationPeriod::query()->delete(); // soft-delete all

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/periods')
        ->assertOk();

    expect($response->json('data'))->toBeEmpty();
});

it('includes the seeded active period in the list', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/periods')
        ->assertOk();

    $data = $response->json('data');
    expect(collect($data)->firstWhere('isActive', true))->not->toBeNull();
});

it('filters periods by is_active', function (): void {
    OrganizationPeriod::factory()->create(['is_active' => false]);
    OrganizationPeriod::factory()->create(['is_active' => false]);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/periods?filter[is_active]=1')
        ->assertOk();

    // Only the seeder's active period should match.
    expect(collect($response->json('data'))->every(fn ($p) => $p['isActive'] === true))->toBeTrue();
});

it('searches periods by label', function (): void {
    OrganizationPeriod::factory()->create(['label' => 'Periode Khusus 2099', 'is_active' => false]);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/periods?search=Periode Khusus 2099')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.label'))->toBe('Periode Khusus 2099');
});

// ─── Show ─────────────────────────────────────────────────────────────────────

it('shows a period by id', function (): void {
    $period = OrganizationPeriod::factory()->create([
        'label' => 'Periode Show Test',
        'start_date' => '2026-01-01',
        'end_date' => '2028-12-31',
        'is_active' => false,
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->getJson("/api/v1/admin/organization/periods/{$period->id}")
        ->assertOk();

    $response->assertJsonPath('data.id', $period->id)
        ->assertJsonPath('data.label', 'Periode Show Test')
        ->assertJsonPath('data.startDate', '2026-01-01')
        ->assertJsonPath('data.endDate', '2028-12-31')
        ->assertJsonPath('data.isActive', false);
});

it('returns 404 for a nonexistent period', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/periods/'.Str::uuid7())
        ->assertStatus(404);
});

// ─── Create ───────────────────────────────────────────────────────────────────

it('creates a period and returns 201 with correct shape', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/organization/periods', [
            'label' => 'Periode 2026-2028',
            'startDate' => '2026-01-01',
            'endDate' => '2028-12-31',
        ])
        ->assertCreated();

    $response->assertJsonPath('data.label', 'Periode 2026-2028')
        ->assertJsonPath('data.startDate', '2026-01-01')
        ->assertJsonPath('data.endDate', '2028-12-31')
        ->assertJsonPath('data.isActive', false);

    $this->assertDatabaseHas('organization_periods', ['label' => 'Periode 2026-2028']);
});

it('validates required fields on create', function (): void {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/organization/periods', [])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['label', 'startDate', 'endDate']]]);
});

it('validates endDate must be after or equal startDate', function (): void {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/organization/periods', [
            'label' => 'Invalid Range',
            'startDate' => '2028-01-01',
            'endDate' => '2026-01-01', // before startDate
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['endDate']]]);
});

// ─── Update ───────────────────────────────────────────────────────────────────

it('updates a period label and dates', function (): void {
    $period = OrganizationPeriod::factory()->create(['label' => 'Old Label', 'is_active' => false]);

    $response = $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/organization/periods/{$period->id}", [
            'label' => 'New Label',
            'startDate' => '2025-01-01',
            'endDate' => '2027-12-31',
        ])
        ->assertOk();

    $response->assertJsonPath('data.label', 'New Label');
    $this->assertDatabaseHas('organization_periods', ['id' => $period->id, 'label' => 'New Label']);
});

// ─── Delete / Restore ────────────────────────────────────────────────────────

it('soft deletes a period', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/organization/periods/{$period->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $period->id);

    $this->assertSoftDeleted('organization_periods', ['id' => $period->id]);
});

it('restores a soft-deleted period', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    $period->delete();

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/organization/periods/{$period->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('organization_periods', ['id' => $period->id, 'deleted_at' => null]);
});

// ─── Activate ────────────────────────────────────────────────────────────────

it('activates a period and deactivates the previously active one', function (): void {
    // Seeder already created one active period. Create a second (inactive).
    $newPeriod = OrganizationPeriod::factory()->create(['is_active' => false]);

    $response = $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/organization/periods/{$newPeriod->id}/activate")
        ->assertOk();

    $response->assertJsonPath('data.id', $newPeriod->id)
        ->assertJsonPath('data.isActive', true);

    // The previously active period must now be inactive.
    $this->assertDatabaseHas('organization_periods', [
        'label' => 'Periode 2023-2025',
        'is_active' => false,
    ]);

    // Only one active period must exist (enforced by DB constraint too).
    expect(OrganizationPeriod::where('is_active', true)->count())->toBe(1);
});

it('is idempotent when activating the already-active period', function (): void {
    $activePeriod = OrganizationPeriod::where('is_active', true)->firstOrFail();

    $response = $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/organization/periods/{$activePeriod->id}/activate")
        ->assertOk();

    $response->assertJsonPath('data.isActive', true);
    expect(OrganizationPeriod::where('is_active', true)->count())->toBe(1);
});
