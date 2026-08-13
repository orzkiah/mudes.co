<?php

declare(strict_types=1);

use App\Domain\Models\Announcement;
use App\Domain\Models\User;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(AnnouncementSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists only public, non-expired announcements publicly, pinned first', function (): void {
    Announcement::factory()->create(['audience' => 'internal']);
    Announcement::factory()->create(['audience' => 'public', 'expires_at' => now()->subDay(), 'starts_at' => now()->subWeek()]);
    Announcement::factory()->create(['audience' => 'public', 'pinned' => true, 'starts_at' => now()]);

    $response = $this->getJson('/api/v1/public/announcements')->assertOk();

    $data = collect($response->json('data'));
    expect($data->every(fn ($a) => $a['audience'] === 'public'))->toBeTrue();
    expect($data->first()['pinned'])->toBeTrue();
});

it('allows editor to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)->getJson('/api/v1/admin/announcements')->assertOk();

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/announcements', ['title' => 'Should Fail'])
        ->assertStatus(403);
});

it('validates expiry after start date', function (): void {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/announcements', [
            'title' => 'Invalid Expiry',
            'body' => 'Body.',
            'startsAt' => now()->toIso8601String(),
            'expiresAt' => now()->subDay()->toIso8601String(),
        ])
        ->assertStatus(422);
});

it('creates an announcement', function (): void {
    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/announcements', [
            'title' => 'New Announcement',
            'body' => 'Important update.',
            'priority' => 'urgent',
        ])
        ->assertCreated();

    $response->assertJsonPath('data.priority', 'urgent');
});

it('soft deletes and restores an announcement', function (): void {
    $announcement = Announcement::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/announcements/{$announcement->id}")
        ->assertOk();

    $this->assertSoftDeleted('announcements', ['id' => $announcement->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/announcements/{$announcement->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent announcement', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/announcements/'.Str::uuid7())
        ->assertStatus(404);
});
