<?php

declare(strict_types=1);

use App\Domain\Models\StudySchedule;
use App\Domain\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\StudyScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(StudyScheduleSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('lists study schedules publicly without authentication (cursor paginated)', function (): void {
    $response = $this->getJson('/api/v1/public/schedule')->assertOk();

    $response->assertJsonPath('meta.pagination.strategy', 'cursor');
});

it('rejects unauthenticated admin requests', function (): void {
    $this->getJson('/api/v1/admin/schedule')->assertStatus(401);
});

it('rejects editor creating a schedule (write restricted)', function (): void {
    $this->actingAs($this->editor)
        ->postJson('/api/v1/admin/schedule', ['title' => 'Should Fail'])
        ->assertStatus(403);
});

it('creates a study schedule with the expanded category object', function (): void {
    $category = StudySchedule::factory()->create()->category;

    $response = $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/schedule', [
            'studyCategoryId' => $category->id,
            'dayOfWeek' => 3,
            'startTime' => '19:00',
            'endTime' => '21:00',
            'ustadzName' => 'Ust. Fulan',
        ])
        ->assertCreated();

    $response->assertJsonPath('data.category.id', $category->id)
        ->assertJsonPath('data.dayOfWeek', 3);
});

it('validates end time after start time', function (): void {
    $schedule = StudySchedule::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson('/api/v1/admin/schedule', [
            'studyCategoryId' => $schedule->study_category_id,
            'dayOfWeek' => 1,
            'startTime' => '20:00',
            'endTime' => '19:00',
            'ustadzName' => 'Ust. Fulan',
        ])
        ->assertStatus(422);
});

it('generates occurrences for a schedule idempotently', function (): void {
    $schedule = StudySchedule::factory()->create(['day_of_week' => now()->dayOfWeek]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/schedule/{$schedule->id}/occurrences/generate?weeks=4")
        ->assertOk()
        ->assertJsonPath('data.created', 4);

    // Running again should create nothing new (idempotent).
    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/schedule/{$schedule->id}/occurrences/generate?weeks=4")
        ->assertOk()
        ->assertJsonPath('data.created', 0);
});

it('soft deletes and restores a study schedule', function (): void {
    $schedule = StudySchedule::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/v1/admin/schedule/{$schedule->id}")
        ->assertOk();

    $this->assertSoftDeleted('study_schedules', ['id' => $schedule->id]);

    $this->actingAs($this->superAdmin)
        ->postJson("/api/v1/admin/schedule/{$schedule->id}/restore")
        ->assertOk();

    $this->assertDatabaseHas('study_schedules', ['id' => $schedule->id, 'deleted_at' => null]);
});

it('returns 404 for a nonexistent study schedule', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/schedule/'.Str::uuid7())
        ->assertStatus(404);
});
