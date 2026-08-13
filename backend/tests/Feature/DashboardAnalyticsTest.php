<?php

declare(strict_types=1);

use App\Domain\Models\User;
use Database\Seeders\DashboardAnalyticsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(DashboardAnalyticsSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('rejects unauthenticated requests', function (): void {
    $this->getJson('/api/v1/admin/dashboard/summary')->assertStatus(401);
});

it('rejects a role without dashboard.view permission', function (): void {
    $this->actingAs($this->editor)
        ->getJson('/api/v1/admin/dashboard/summary')
        ->assertStatus(403);
});

it('returns the dashboard summary', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/dashboard/summary')
        ->assertOk()
        ->assertJsonStructure(['data' => ['totalMembers', 'totalArticles', 'totalActivities', 'totalAttendances', 'growth']]);
});

it('returns the attendance trend', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/dashboard/attendance-trend')
        ->assertOk();
});

it('returns content volume', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/dashboard/content-volume')
        ->assertOk();
});

it('returns library engagement', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/dashboard/library-engagement')
        ->assertOk();
});

it('returns activity participation', function (): void {
    $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/dashboard/activity-participation')
        ->assertOk();
});
