<?php

declare(strict_types=1);

use App\Domain\Models\Department;
use App\Domain\Models\OrganizationPeriod;
use App\Domain\Models\OrganizationPosition;
use App\Domain\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(DepartmentSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists active departments publicly without authentication', function (): void {
    Department::factory()->create(['is_active' => true]);
    Department::factory()->create(['is_active' => false]);

    $response = $this->getJson('/api/v1/public/departments')->assertOk();

    $names = collect($response->json('data'))->pluck('isActive');
    expect($names->every(fn ($active) => $active === true))->toBeTrue();
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/departments')->assertStatus(401);
});

it('allows editor to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)
        ->getJson('/api/v1/admin/departments')
        ->assertOk();

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/departments', ['name' => 'Should Fail'])
        ->assertStatus(403);
});

it('lists departments for a super admin with pagination envelope', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/departments?perPage=5')
        ->assertOk();

    $response->assertJsonPath('success', true)
        ->assertJsonPath('meta.pagination.strategy', 'offset')
        ->assertJsonPath('meta.pagination.perPage', 5);
});

it('filters departments by is_active', function (): void {
    Department::factory()->count(2)->create(['is_active' => false]);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/departments?filter[is_active]=0')
        ->assertOk();

    expect(collect($response->json('data'))->every(fn ($d) => $d['isActive'] === false))->toBeTrue();
});

it('searches departments by name', function (): void {
    Department::factory()->create(['name' => 'Unique Search Department']);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/departments?search=Unique Search Department')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('creates a department with auto-generated slug', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments', ['name' => 'Bidang Test Baru'])
        ->assertCreated();

    $response->assertJsonPath('data.slug', 'bidang-test-baru');
    $this->assertDatabaseHas('departments', ['name' => 'Bidang Test Baru']);
});

it('validates required fields and hex color format on create', function (): void {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments', ['color' => 'not-a-hex-color'])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['name', 'color']]]);
});

it('rejects a duplicate department name', function (): void {
    Department::factory()->create(['name' => 'Duplicate Name']);

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments', ['name' => 'Duplicate Name'])
        ->assertStatus(422);
});

it('partially updates a department via PATCH without requiring other fields', function (): void {
    $department = Department::factory()->create(['name' => 'Original Name', 'description' => 'Original description']);

    $response = $this->actingAs($this->superAdmin)
        ->patchJson("/api/v1/admin/departments/{$department->id}", ['name' => 'Updated Name'])
        ->assertOk();

    $response->assertJsonPath('data.name', 'Updated Name');
    $response->assertJsonPath('data.description', 'Original description');
});

it('soft deletes and restores a department', function (): void {
    $department = Department::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/departments/{$department->id}")
        ->assertOk();

    $this->assertSoftDeleted('departments', ['id' => $department->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/departments/{$department->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('departments', ['id' => $department->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent department', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/departments/'.Str::uuid7())
        ->assertStatus(404);
});

it('bulk activates and deactivates departments', function (): void {
    $departments = Department::factory()->count(3)->create(['is_active' => true]);
    $ids = $departments->pluck('id')->all();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments/bulk-deactivate', ['ids' => $ids])
        ->assertOk()
        ->assertJsonPath('data.updated', 3);

    foreach ($departments as $department) {
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'is_active' => false]);
    }

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments/bulk-activate', ['ids' => $ids])
        ->assertOk()
        ->assertJsonPath('data.updated', 3);
});

it('bulk deletes and bulk restores departments', function (): void {
    $departments = Department::factory()->count(2)->create();
    $ids = $departments->pluck('id')->all();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments/bulk-delete', ['ids' => $ids])
        ->assertOk()
        ->assertJsonPath('data.deleted', 2);

    foreach ($departments as $department) {
        $this->assertSoftDeleted('departments', ['id' => $department->id]);
    }

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments/bulk-restore', ['ids' => $ids])
        ->assertOk();

    foreach ($departments as $department) {
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'deleted_at' => null]);
    }
});

it('rejects a bulk request with more than 100 ids', function (): void {
    $ids = collect(range(1, 101))->map(fn () => (string) Str::uuid7())->all();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments/bulk-activate', ['ids' => $ids])
        ->assertStatus(422);
});

it('reorders departments', function (): void {
    $first = Department::factory()->create(['display_order' => 0]);
    $second = Department::factory()->create(['display_order' => 1]);

    $this->actingAs($this->superAdmin)
        ->putJson('/api/v1/admin/departments/bulk-reorder', ['order' => [$second->id, $first->id]])
        ->assertOk();

    expect($first->fresh()->display_order)->toBe(1);
    expect($second->fresh()->display_order)->toBe(0);
});

it('records audit columns on create', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/departments', ['name' => 'Audit Test Department'])
        ->assertCreated();

    $department = Department::findOrFail($response->json('data.id'));

    expect($department->created_by)->toBe($this->superAdmin->id);
    expect($department->updated_by)->toBe($this->superAdmin->id);
});

it('reflects real organization positions in positionCount and blocks deletion while any exist', function (): void {
    $department = Department::factory()->create();
    $period = OrganizationPeriod::factory()->create(['is_active' => false]);
    OrganizationPosition::factory()->create(['organization_period_id' => $period->id, 'department_id' => $department->id]);

    $response = $this->actingAs($this->superAdmin)
        ->getJson("/api/v1/admin/departments/{$department->id}")
        ->assertOk();

    $response->assertJsonPath('data.positionCount', 1);

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/departments/{$department->id}")
        ->assertStatus(409)
        ->assertJsonPath('errors.type', 'urn:mudes:error:dependency-conflict');
});
