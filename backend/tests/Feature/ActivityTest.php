<?php

declare(strict_types=1);

use App\Domain\Models\Activity;
use App\Domain\Models\User;
use Database\Seeders\ActivitySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(ActivitySeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists activities publicly without authentication (cursor paginated)', function (): void {
    $response = $this->getJson('/api/v1/public/activities')->assertOk();

    $response->assertJsonPath('meta.pagination.strategy', 'cursor');
});

it('shows an activity publicly by slug', function (): void {
    $activity = Activity::factory()->create(['title' => 'Public Slug Test', 'slug' => 'public-slug-test']);

    $this->getJson('/api/v1/public/activities/public-slug-test')
        ->assertOk()
        ->assertJsonPath('data.id', $activity->id);
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/activities')->assertStatus(401);
});

it('allows editor to view but not write (read-all-write-restricted matrix)', function (): void {
    $this->actingAs($this->editor)->getJson('/api/v1/admin/activities')->assertOk();

    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/activities', ['title' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates an activity with the expanded category object', function (): void {
    $activity = Activity::factory()->create();
    $categoryId = $activity->activity_category_id;

    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/activities', [
            'activityCategoryId' => $categoryId,
            'title' => 'New Activity',
            'startAt' => now()->addWeek()->toIso8601String(),
        ])
        ->assertCreated();

    $response->assertJsonPath('data.category.id', $categoryId)
        ->assertJsonPath('data.status', 'upcoming');
});

it('validates end date after start date', function (): void {
    $activity = Activity::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/activities', [
            'activityCategoryId' => $activity->activity_category_id,
            'title' => 'Invalid Dates',
            'startAt' => now()->toIso8601String(),
            'endAt' => now()->subDay()->toIso8601String(),
        ])
        ->assertStatus(422);
});

it('soft deletes and restores an activity', function (): void {
    $activity = Activity::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/activities/{$activity->id}")
        ->assertOk();

    $this->assertSoftDeleted('activities', ['id' => $activity->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/activities/{$activity->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('activities', ['id' => $activity->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent activity', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/activities/'.Str::uuid7())
        ->assertStatus(404);
});
