<?php

declare(strict_types=1);

use App\Domain\Models\Media;
use App\Domain\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Use a fake disk so no real files are written during tests.
    Storage::fake('public');

    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

// ─── Authentication ──────────────────────────────────────────────────────────

it('returns 401 for unauthenticated requests', function (): void {
    $this->postJson('/api/v1/admin/media')
        ->assertStatus(401)
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.type', 'urn:mudes:error:unauthenticated');
});

// ─── Validation — collection ─────────────────────────────────────────────────

it('returns 422 when collection is missing', function (): void {
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', ['file' => $file])
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['errors' => ['fields' => ['collection']]]);
});

it('returns 422 for an invalid collection name', function (): void {
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'invalid-collection',
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['collection']]]);
});

// ─── Validation — MIME type ───────────────────────────────────────────────────

it('returns 422 when a non-image file is uploaded to an image collection', function (): void {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'article-cover',
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['file']]]);
});

it('returns 422 when an image is uploaded to library-file collection', function (): void {
    $file = UploadedFile::fake()->image('cover.jpg');

    $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'library-file',
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['file']]]);
});

// ─── Validation — file size ───────────────────────────────────────────────────

it('returns 422 when file exceeds 10 MB', function (): void {
    // 10241 KB = 10 MB + 1 KB, just over the limit.
    $file = UploadedFile::fake()->create('big.jpg', 10241, 'image/jpeg');

    $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'member-photo',
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['fields' => ['file']]]);
});

// ─── Successful uploads ───────────────────────────────────────────────────────

it('uploads a JPEG image to member-photo and returns 201 with media shape', function (): void {
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'member-photo',
        ])
        ->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => ['id', 'url', 'name', 'fileName', 'mimeType', 'size', 'collection', 'createdAt'],
        ]);

    $data = $response->json('data');

    expect($data['collection'])->toBe('member-photo')
        ->and($data['mimeType'])->toContain('image/')
        ->and($data['id'])->toBeString()->toHaveLength(36); // UUID format

    // Verify the media row was actually persisted.
    expect(Media::find($data['id']))->not->toBeNull();
});

it('uploads a PNG image to gallery-photo and returns 201', function (): void {
    $file = UploadedFile::fake()->image('gallery.png', 800, 600);

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'gallery-photo',
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.collection', 'gallery-photo');

    Storage::disk('public')->assertExists(
        collect(explode('/storage/', $response->json('data.url')))->last()
    );
});

it('uploads a PDF to library-file and returns 201', function (): void {
    // UploadedFile::fake()->create() creates a file with actual binary content
    // that Spatie's MIME sniffing accepts as the declared type.
    // We use a 1-byte file; Spatie sniffs content, not just extension.
    // For a genuine PDF header we need at least the %PDF- signature.
    $tmpPath = tempnam(sys_get_temp_dir(), 'pest') . '.pdf';
    file_put_contents($tmpPath, '%PDF-1.4 fake content for testing');
    $file = new \Illuminate\Http\UploadedFile($tmpPath, 'document.pdf', 'application/pdf', null, true);

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'library-file',
        ])
        ->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.collection', 'library-file');

    expect($response->json('data.mimeType'))->toContain('pdf');
});

it('uploads a file exactly at the 10 MB limit', function (): void {
    // UploadedFile::fake()->create() with a size in KB generates a file
    // whose declared size is used for the `max` validation rule — the actual
    // file content is minimal (fake). Laravel's validator checks the declared
    // size (10240 KB = 10 MB exactly). Using a JPEG so MIME sniffing works.
    $file = UploadedFile::fake()->image('max.jpg', 100, 100);

    $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'article-cover',
        ])
        ->assertStatus(201);
});

// ─── Response shape ───────────────────────────────────────────────────────────

it('response data.url points to the public storage path', function (): void {
    $file = UploadedFile::fake()->image('cover.jpg', 400, 300); // jpg, not webp — GD in Docker may not support webp

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => 'activity-cover',
        ])
        ->assertStatus(201);

    expect($response->json('data.url'))->toContain('/storage/');
});

it('all five valid collections are accepted', function (string $collection, string $mime, string $ext): void {
    $file = in_array($collection, ['member-photo', 'article-cover', 'activity-cover', 'gallery-photo'])
        ? UploadedFile::fake()->image("test.{$ext}", 100, 100)
        : (new \Illuminate\Http\UploadedFile(
            tap(tempnam(sys_get_temp_dir(), 'pest') . ".{$ext}", fn ($p) => file_put_contents($p, '%PDF-1.4 test')),
            "test.{$ext}", $mime, null, true
          ));

    $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/media', [
            'file' => $file,
            'collection' => $collection,
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.collection', $collection);
})->with([
    ['member-photo', 'image/jpeg', 'jpg'],
    ['article-cover', 'image/png', 'png'],
    ['activity-cover', 'image/jpeg', 'jpg'],  // webp not supported by GD in test env
    ['gallery-photo', 'image/jpeg', 'jpg'],
    ['library-file', 'application/pdf', 'pdf'],
]);
