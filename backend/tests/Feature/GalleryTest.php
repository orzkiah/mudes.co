<?php

declare(strict_types=1);

use App\Domain\Models\Gallery;
use App\Domain\Models\Media;
use App\Domain\Models\User;
use Database\Seeders\GallerySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(GallerySeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists galleries publicly without authentication (cursor paginated)', function (): void {
    $response = $this->getJson('/api/v1/public/galleries')->assertOk();

    $response->assertJsonPath('meta.pagination.strategy', 'cursor');
});

it('allows editor to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)->getJson('/api/v1/admin/galleries')->assertOk();

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/galleries', ['title' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates a gallery album', function (): void {
    $gallery = Gallery::factory()->create();

    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/galleries', [
            'galleryCategoryId' => $gallery->gallery_category_id,
            'title' => 'New Album',
        ])
        ->assertCreated();

    $response->assertJsonPath('data.title', 'New Album')
        ->assertJsonPath('data.photoCount', 0);
});

it('attaches, reorders, and removes photos from an album', function (): void {
    $gallery = Gallery::factory()->create();

    $mediaOne = Media::query()->forceCreate([
        'id' => (string) Str::uuid7(), 'model_type' => Gallery::class, 'model_id' => $gallery->id,
        'collection_name' => 'gallery-photo', 'name' => 'one', 'file_name' => 'one.jpg', 'mime_type' => 'image/jpeg',
        'disk' => 'public', 'size' => 100, 'manipulations' => [], 'custom_properties' => [],
        'generated_conversions' => [], 'responsive_images' => [],
    ]);
    $mediaTwo = Media::query()->forceCreate([
        'id' => (string) Str::uuid7(), 'model_type' => Gallery::class, 'model_id' => $gallery->id,
        'collection_name' => 'gallery-photo', 'name' => 'two', 'file_name' => 'two.jpg', 'mime_type' => 'image/jpeg',
        'disk' => 'public', 'size' => 100, 'manipulations' => [], 'custom_properties' => [],
        'generated_conversions' => [], 'responsive_images' => [],
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/galleries/{$gallery->id}/photos", ['mediaIds' => [$mediaOne->id, $mediaTwo->id]])
        ->assertCreated();

    $response->assertJsonPath('data.photoCount', 2);

    $photoIds = collect($response->json('data.photos'))->pluck('id')->all();

    $this->actingAs($this->superAdmin)
        ->putJson("/api/v1/admin/galleries/{$gallery->id}/photos/reorder", ['order' => array_reverse($photoIds)])
        ->assertOk();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/galleries/{$gallery->id}/photos/{$photoIds[0]}")
        ->assertOk();

    expect($gallery->fresh()->photos()->count())->toBe(1);
});

it('soft deletes and restores a gallery', function (): void {
    $gallery = Gallery::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/galleries/{$gallery->id}")
        ->assertOk();

    $this->assertSoftDeleted('galleries', ['id' => $gallery->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/galleries/{$gallery->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('galleries', ['id' => $gallery->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent gallery', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/galleries/'.Str::uuid7())
        ->assertStatus(404);
});
