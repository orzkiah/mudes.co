<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use App\Domain\Models\StudyCategory;
use App\Domain\Models\StudySchedule;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.10 - view all +
 * public, write Super Admin/Ketua/Sekretaris) and a sample recurring
 * schedule entry.
 */
class StudyScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'study-schedules.view',
            'study-schedules.create',
            'study-schedules.update',
            'study-schedules.delete',
            'study-schedules.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('study-schedules.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $category = StudyCategory::where('name', 'Weekly Study')->first();

        if ($category !== null) {
            StudySchedule::firstOrCreate(
                ['study_category_id' => $category->id, 'day_of_week' => 0],
                [
                    'start_time' => '19:00:00',
                    'end_time' => '21:00:00',
                    'topic' => 'Kajian Rutin Mingguan',
                    'ustadz_name' => 'Ust. Abdullah',
                    'location' => 'Masjid Al-Ikhlas',
                    'is_active' => true,
                ],
            );
        }
    }
}
