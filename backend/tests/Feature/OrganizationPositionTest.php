<?php

declare(strict_types=1);

use App\Domain\Models\OrganizationPeriod;
use App\Domain\Models\OrganizationPosition;
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

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('rejects unauthenticated requests', function (): void {
    $this->getJson('/api/v1/admin/organization/positions')->assertStatus(401);
});

it('allows editor to view but rejects editor creating a position (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)
        ->getJson('/api/v1/admin/organization/positions')
        ->assertOk();

    $period = OrganizationPeriod::factory()->create(['is_active' => false]);

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/organization/positions', [
            'organizationPeriodId' => $period->id,
            'title' => 'Should Fail',
            'positionType' => 'member',
        ])
        ->assertStatus(403);
});

it('shows the active period structure publicly as a nested tree', function (): void {
    $response = $this->getJson('/api/v1/public/organization/structure')->assertOk();

    $roots = $response->json('data');
    expect($roots)->toHaveCount(1);
    expect($roots[0]['title'])->toBe('Ketua');
    expect($roots[0]['children'])->toHaveCount(2);
});

it('lists positions for a super admin with the pagination envelope', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/positions?perPage=5')
        ->assertOk();

    $response->assertJsonPath('success', true)
        ->assertJsonPath('meta.pagination.strategy', 'offset')
        ->assertJsonPath('meta.pagination.perPage', 5);
});

it('filters positions by position type', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/positions?filter[position_type]=secretary')
        ->assertOk();

    expect(collect($response->json('data'))->every(fn ($p) => $p['positionType'] === 'secretary'))->toBeTrue();
});

it('searches positions by title', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'title' => 'Unique Search Position']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/positions?search=Unique Search Position')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('creates a root position and a child position with the correct computed level', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);

    $root = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/organization/positions', [
            'organizationPeriodId' => $period->id,
            'title' => 'Ketua Baru',
            'positionType' => 'chairman',
        ])
        ->assertCreated();

    $root->assertJsonPath('data.level', 0);

    $child = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/organization/positions', [
            'organizationPeriodId' => $period->id,
            'parentPositionId' => $root->json('data.id'),
            'title' => 'Wakil Ketua Baru',
            'positionType' => 'vice_chairman',
        ])
        ->assertCreated();

    $child->assertJsonPath('data.level', 1);
});

it('validates required fields and position type on create', function (): void {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/organization/positions', ['positionType' => 'not-a-type'])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['organizationPeriodId', 'title', 'positionType']]]);
});

it('updates a position', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    $position = OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'title' => 'Old Title']);

    $response = $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/organization/positions/{$position->id}", [
            'organizationPeriodId' => $period->id,
            'title' => 'New Title',
            'positionType' => 'member',
        ])
        ->assertOk();

    $response->assertJsonPath('data.title', 'New Title');
});

it('recomputes level for the entire subtree when reparenting', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);

    $root = OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'level' => 0]);
    $branchA = OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'parent_position_id' => $root->id, 'level' => 1]);
    $branchB = OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'level' => 0]);
    $grandchild = OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'parent_position_id' => $branchA->id, 'level' => 2]);

    // Move branchA (and its child grandchild) under branchB instead of root.
    $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/organization/positions/{$branchA->id}", [
            'organizationPeriodId' => $period->id,
            'parentPositionId' => $branchB->id,
            'title' => $branchA->title,
            'positionType' => 'member',
        ])
        ->assertOk()
        ->assertJsonPath('data.level', 1);

    expect($grandchild->fresh()->level)->toBe(2);
});

it('rejects setting a position as its own parent', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    $position = OrganizationPosition::factory()->create(['organization_period_id' => $period->id]);

    $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/organization/positions/{$position->id}", [
            'organizationPeriodId' => $period->id,
            'parentPositionId' => $position->id,
            'title' => $position->title,
            'positionType' => 'member',
        ])
        ->assertStatus(409)
        ->assertJsonPath('errors.type', 'urn:mudes:error:cycle-detected');
});

it('rejects moving a position under its own descendant', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    $root = OrganizationPosition::factory()->create(['organization_period_id' => $period->id]);
    $child = OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'parent_position_id' => $root->id, 'level' => 1]);

    $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/organization/positions/{$root->id}", [
            'organizationPeriodId' => $period->id,
            'parentPositionId' => $child->id,
            'title' => $root->title,
            'positionType' => 'member',
        ])
        ->assertStatus(409)
        ->assertJsonPath('errors.type', 'urn:mudes:error:cycle-detected');
});

it('reorders a position and reports the affected descendant count', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    $oldParent = OrganizationPosition::factory()->create(['organization_period_id' => $period->id]);
    $newParent = OrganizationPosition::factory()->create(['organization_period_id' => $period->id]);
    $position = OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'parent_position_id' => $oldParent->id, 'level' => 1]);
    OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'parent_position_id' => $position->id, 'level' => 2]);

    $response = $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/organization/positions/{$position->id}/reorder", [
            'displayOrder' => 2,
            'parentPositionId' => $newParent->id,
        ])
        ->assertOk();

    $response->assertJsonPath('data.displayOrder', 2)
        ->assertJsonPath('data.parentPositionId', $newParent->id)
        ->assertJsonPath('data.affectedDescendantCount', 1);
});

it('rejects deleting a position that still has child positions', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    $root = OrganizationPosition::factory()->create(['organization_period_id' => $period->id]);
    OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'parent_position_id' => $root->id, 'level' => 1]);

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/organization/positions/{$root->id}")
        ->assertStatus(409)
        ->assertJsonPath('errors.type', 'urn:mudes:error:dependency-conflict');
});

it('soft deletes and restores a leaf position', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    $position = OrganizationPosition::factory()->create(['organization_period_id' => $period->id]);

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/organization/positions/{$position->id}")
        ->assertOk();

    $this->assertSoftDeleted('organization_positions', ['id' => $position->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/organization/positions/{$position->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('organization_positions', ['id' => $position->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent position', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/organization/positions/'.Str::uuid7())
        ->assertStatus(404);
});

it('records audit columns on create', function (): void {
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);

    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/organization/positions', [
            'organizationPeriodId' => $period->id,
            'title' => 'Audit Test Position',
            'positionType' => 'member',
        ])
        ->assertCreated();

    $position = OrganizationPosition::findOrFail($response->json('data.id'));

    expect($position->created_by)->toBe($this->superAdmin->id);
    expect($position->updated_by)->toBe($this->superAdmin->id);
});

it('returns the full nested tree for a given period via the admin tree endpoint', function (): void {
    $period = OrganizationPosition::query()->first()->organization_period_id;

    $response = $this->actingAs($this->superAdmin)
        ->getJson("/api/v1/admin/organization/positions/tree?organizationPeriodId={$period}")
        ->assertOk();

    $roots = $response->json('data');
    expect($roots)->toHaveCount(1);
    expect($roots[0]['children'])->toHaveCount(2);
});
