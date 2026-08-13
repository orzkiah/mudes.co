<?php

declare(strict_types=1);

use App\Domain\Models\GalleryCategory;
use App\Domain\Models\User;
use Database\Seeders\GalleryCategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(GalleryCategorySeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists active gallery categories publicly without authentication', function (): void {
    $this->getJson('/api/v1/public/gallery-categories')->assertOk();
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/gallery-categories')->assertStatus(401);
});

it('allows editor to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)->getJson('/api/v1/admin/gallery-categories')->assertOk();

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/gallery-categories', ['name' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates a gallery category with auto-generated slug', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/gallery-categories', ['name' => 'Wisuda Tahfidz'])
        ->assertCreated();

    $response->assertJsonPath('data.slug', 'wisuda-tahfidz')
        ->assertJsonPath('data.galleryCount', 0);
});

it('partially updates a gallery category via PATCH', function (): void {
    $category = GalleryCategory::factory()->create(['name' => 'Original', 'is_active' => true]);

    $this->actingAs($this->superAdmin)
        ->patchJson("/api/v1/admin/gallery-categories/{$category->id}", ['isActive' => false])
        ->assertOk()
        ->assertJsonPath('data.isActive', false)
        ->assertJsonPath('data.name', 'Original');
});

it('soft deletes and restores a gallery category', function (): void {
    $category = GalleryCategory::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/gallery-categories/{$category->id}")
        ->assertOk();

    $this->assertSoftDeleted('gallery_categories', ['id' => $category->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/gallery-categories/{$category->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('gallery_categories', ['id' => $category->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent gallery category', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/gallery-categories/'.Str::uuid7())
        ->assertStatus(404);
});
