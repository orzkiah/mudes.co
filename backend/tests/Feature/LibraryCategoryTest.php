<?php

declare(strict_types=1);

use App\Domain\Models\LibraryCategory;
use App\Domain\Models\User;
use Database\Seeders\LibraryCategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(LibraryCategorySeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    // 'humas' is not among library-categories' writer roles (Super
    // Admin/Sekretaris/Multimedia/Editor), unlike most other taxonomy
    // modules where 'editor' is the non-writer example.
    $this->nonWriter = User::factory()->create();
    $this->nonWriter->assignRole('humas');
});

it('lists active library categories publicly without authentication', function (): void {
    $this->getJson('/api/v1/public/library-categories')->assertOk();
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/library-categories')->assertStatus(401);
});

it('allows a non-writer role to view but not write', function (): void {
    $this->actingAs($this->nonWriter)->getJson('/api/v1/admin/library-categories')->assertOk();

    $this->actingAs($this->nonWriter)
        ->postJson('/api/v1/admin/library-categories', ['name' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates a library category with auto-generated slug', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/library-categories', ['name' => 'Fiqih'])
        ->assertCreated();

    $response->assertJsonPath('data.slug', 'fiqih')
        ->assertJsonPath('data.documentCount', 0);
});

it('partially updates a library category via PATCH', function (): void {
    $category = LibraryCategory::factory()->create(['name' => 'Original', 'is_active' => true]);

    $this->actingAs($this->superAdmin)
        ->patchJson("/api/v1/admin/library-categories/{$category->id}", ['isActive' => false])
        ->assertOk()
        ->assertJsonPath('data.isActive', false)
        ->assertJsonPath('data.name', 'Original');
});

it('soft deletes and restores a library category', function (): void {
    $category = LibraryCategory::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/library-categories/{$category->id}")
        ->assertOk();

    $this->assertSoftDeleted('library_categories', ['id' => $category->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/library-categories/{$category->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('library_categories', ['id' => $category->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent library category', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/library-categories/'.Str::uuid7())
        ->assertStatus(404);
});
