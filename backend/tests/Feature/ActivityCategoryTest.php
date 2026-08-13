<?php

declare(strict_types=1);

use App\Domain\Models\ActivityCategory;
use App\Domain\Models\User;
use Database\Seeders\ActivityCategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(ActivityCategorySeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists active activity categories publicly without authentication', function (): void {
    $this->getJson('/api/v1/public/activity-categories')->assertOk();
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/activity-categories')->assertStatus(401);
});

it('allows editor to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)->getJson('/api/v1/admin/activity-categories')->assertOk();

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/activity-categories', ['name' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates an activity category with auto-generated slug', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/activity-categories', ['name' => 'Donor Darah'])
        ->assertCreated();

    $response->assertJsonPath('data.slug', 'donor-darah')
        ->assertJsonPath('data.activityCount', 0);
});

it('partially updates an activity category via PATCH', function (): void {
    $category = ActivityCategory::factory()->create(['name' => 'Original', 'is_active' => true]);

    $this->actingAs($this->superAdmin)
        ->patchJson("/api/v1/admin/activity-categories/{$category->id}", ['isActive' => false])
        ->assertOk()
        ->assertJsonPath('data.isActive', false)
        ->assertJsonPath('data.name', 'Original');
});

it('soft deletes and restores an activity category', function (): void {
    $category = ActivityCategory::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/activity-categories/{$category->id}")
        ->assertOk();

    $this->assertSoftDeleted('activity_categories', ['id' => $category->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/activity-categories/{$category->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('activity_categories', ['id' => $category->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent activity category', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/activity-categories/'.Str::uuid7())
        ->assertStatus(404);
});
