<?php

declare(strict_types=1);

use App\Domain\Models\StudyCategory;
use App\Domain\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\StudyCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(StudyCategorySeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists active study categories publicly without authentication', function (): void {
    $this->getJson('/api/v1/public/study-categories')->assertOk();
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/study-categories')->assertStatus(401);
});

it('allows editor to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)->getJson('/api/v1/admin/study-categories')->assertOk();

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/study-categories', ['name' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates a study category with auto-generated slug', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/study-categories', ['name' => 'Kajian Khusus'])
        ->assertCreated();

    $response->assertJsonPath('data.slug', 'kajian-khusus')
        ->assertJsonPath('data.scheduleCount', 0);
});

it('partially updates a study category via PATCH', function (): void {
    $category = StudyCategory::factory()->create(['name' => 'Original', 'is_active' => true]);

    $this->actingAs($this->superAdmin)
        ->patchJson("/api/v1/admin/study-categories/{$category->id}", ['isActive' => false])
        ->assertOk()
        ->assertJsonPath('data.isActive', false)
        ->assertJsonPath('data.name', 'Original');
});

it('soft deletes and restores a study category', function (): void {
    $category = StudyCategory::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/study-categories/{$category->id}")
        ->assertOk();

    $this->assertSoftDeleted('study_categories', ['id' => $category->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/study-categories/{$category->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('study_categories', ['id' => $category->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent study category', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/study-categories/'.Str::uuid7())
        ->assertStatus(404);
});
