<?php

declare(strict_types=1);

use App\Domain\Models\LibraryDocument;
use App\Domain\Models\User;
use Database\Seeders\LibraryDocumentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(LibraryDocumentSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    // 'humas' lacks library-documents.view (restricted to Super
    // Admin/Sekretaris/Multimedia/Editor).
    $this->nonViewer = User::factory()->create();
    $this->nonViewer->assignRole('humas');
});

it('lists only public documents on the public endpoint', function (): void {
    LibraryDocument::factory()->create(['visibility' => 'internal']);

    $response = $this->getJson('/api/v1/public/library')->assertOk();

    expect(collect($response->json('data'))->every(fn ($d) => $d['visibility'] === 'public'))->toBeTrue();
});

it('hides an internal document from the public detail endpoint', function (): void {
    $internal = LibraryDocument::factory()->create(['visibility' => 'internal', 'external_url' => 'https://example.com/internal']);

    $this->getJson("/api/v1/public/library/{$internal->id}")->assertStatus(404);
});

it('increments download count when a public document is viewed', function (): void {
    $document = LibraryDocument::factory()->create(['visibility' => 'public', 'download_count' => 0]);

    $this->getJson("/api/v1/public/library/{$document->id}")->assertOk();

    expect($document->fresh()->download_count)->toBe(1);
});

it('rejects a role without library-documents permission', function (): void {
    $this->actingAs($this->nonViewer)
        ->getJson('/api/v1/admin/library')
        ->assertStatus(403);
});

it('rejects a document with both file and external url', function (): void {
    $document = LibraryDocument::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/library', [
            'libraryCategoryId' => $document->library_category_id,
            'title' => 'Conflicting Source',
            'fileMediaId' => (string) Str::uuid7(),
            'externalUrl' => 'https://example.com/conflict',
        ])
        ->assertStatus(422);
});

it('rejects a document with neither file nor external url', function (): void {
    $document = LibraryDocument::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/library', [
            'libraryCategoryId' => $document->library_category_id,
            'title' => 'No Source',
        ])
        ->assertStatus(422);
});

it('creates a document with an external url', function (): void {
    $document = LibraryDocument::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/library', [
            'libraryCategoryId' => $document->library_category_id,
            'title' => 'External Link Document',
            'externalUrl' => 'https://example.com/doc',
        ])
        ->assertCreated()
        ->assertJsonPath('data.libraryType', 'video_link');
});

it('soft deletes and restores a document', function (): void {
    $document = LibraryDocument::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/library/{$document->id}")
        ->assertOk();

    $this->assertSoftDeleted('library_documents', ['id' => $document->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/library/{$document->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('library_documents', ['id' => $document->id, 'deleted_at' => null]);
});
