<?php

use App\Domain\Models\Permission;
use App\Domain\Models\StudyCategory;
use App\Domain\Models\StudySchedule;
use App\Domain\Models\StudyScheduleOccurrence;
use App\Domain\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $permission = Permission::firstOrCreate(['name' => 'study-schedules.view', 'guard_name' => 'web']);
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');
    $this->superAdmin->givePermissionTo($permission);
});

test('admin can retrieve list of study schedule occurrences', function () {
    $category = StudyCategory::factory()->create(['name' => 'Kajian Rutin']);
    $schedule = StudySchedule::factory()->create([
        'study_category_id' => $category->id,
        'topic' => 'Fiqih Muamalah',
        'ustadz_name' => 'Ustadz Ahmad',
        'location' => 'Masjid Condet',
    ]);

    $occurrence = StudyScheduleOccurrence::factory()->create([
        'study_schedule_id' => $schedule->id,
        'occurrence_date' => '2026-08-06',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->getJson('/api/v1/admin/schedule-occurrences');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                [
                    'id' => $occurrence->id,
                    'occurrenceDate' => '2026-08-06',
                    'schedule' => [
                        'topic' => 'Fiqih Muamalah',
                        'ustadzName' => 'Ustadz Ahmad',
                    ],
                ],
            ],
        ]);
});
