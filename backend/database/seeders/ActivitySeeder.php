<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Activity;
use App\Domain\Models\ActivityCategory;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.13,
 * PROJECT_SPECIFICATION.md §3.8 - view all + public, write Super
 * Admin/Ketua/Sekretaris/Humas) and a sample activity.
 */
class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['activities.view', 'activities.create', 'activities.update', 'activities.delete', 'activities.restore'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('activities.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value, RoleName::Humas->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $category = ActivityCategory::where('name', 'Bakti Sosial')->first();
        $title = 'Bakti Sosial Ramadan 2026';

        if ($category !== null) {
            Activity::firstOrCreate(
                ['title' => $title],
                [
                    'activity_category_id' => $category->id,
                    'slug' => Str::slug($title),
                    'description' => 'Kegiatan bakti sosial dalam rangka menyambut bulan Ramadan.',
                    'start_at' => now()->addWeeks(2),
                    'location' => 'Aula Masjid Al-Ikhlas',
                    'status' => 'upcoming',
                ],
            );
        }
    }
}
