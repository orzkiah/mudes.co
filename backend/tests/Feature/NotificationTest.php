<?php

declare(strict_types=1);

use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('rejects unauthenticated requests', function (): void {
    $this->getJson('/api/v1/admin/notifications')->assertStatus(401);
});

it('lists only the caller own notifications, cursor paginated', function (): void {
    $this->user->notifications()->create([
        'id' => (string) Str::orderedUuid(),
        'type' => 'App\\Notifications\\SystemNotice',
        'data' => ['message' => 'For me'],
    ]);
    $this->otherUser->notifications()->create([
        'id' => (string) Str::orderedUuid(),
        'type' => 'App\\Notifications\\SystemNotice',
        'data' => ['message' => 'Not for me'],
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/admin/notifications')
        ->assertOk();

    $response->assertJsonPath('meta.pagination.strategy', 'cursor');
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.data.message'))->toBe('For me');
});

it('reports the unread count', function (): void {
    $this->user->notifications()->create([
        'id' => (string) Str::orderedUuid(),
        'type' => 'App\\Notifications\\SystemNotice',
        'data' => ['message' => 'Unread'],
    ]);

    $this->actingAs($this->user)
        ->getJson('/api/v1/admin/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.count', 1);
});

it('marks a single notification as read', function (): void {
    $notification = $this->user->notifications()->create([
        'id' => (string) Str::orderedUuid(),
        'type' => 'App\\Notifications\\SystemNotice',
        'data' => ['message' => 'To read'],
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/admin/notifications/{$notification->id}/mark-read")
        ->assertOk()
        ->assertJsonPath('data.isRead', true);
});

it('marks all notifications as read', function (): void {
    $this->user->notifications()->create(['id' => (string) Str::orderedUuid(), 'type' => 'App\\Notifications\\SystemNotice', 'data' => ['message' => 'One']]);
    $this->user->notifications()->create(['id' => (string) Str::orderedUuid(), 'type' => 'App\\Notifications\\SystemNotice', 'data' => ['message' => 'Two']]);

    $this->actingAs($this->user)
        ->postJson('/api/v1/admin/notifications/mark-all-read')
        ->assertOk()
        ->assertJsonPath('data.updatedCount', 2);
});

it('rejects deleting a notification belonging to another user', function (): void {
    $notification = $this->otherUser->notifications()->create([
        'id' => (string) Str::orderedUuid(),
        'type' => 'App\\Notifications\\SystemNotice',
        'data' => ['message' => 'Not mine'],
    ]);

    $this->actingAs($this->user)
        ->deleteJson("/api/v1/admin/notifications/{$notification->id}")
        ->assertStatus(404);
});

it('hard deletes an owned notification', function (): void {
    $notification = $this->user->notifications()->create([
        'id' => (string) Str::orderedUuid(),
        'type' => 'App\\Notifications\\SystemNotice',
        'data' => ['message' => 'Delete me'],
    ]);

    $this->actingAs($this->user)
        ->deleteJson("/api/v1/admin/notifications/{$notification->id}")
        ->assertOk();

    $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
});
