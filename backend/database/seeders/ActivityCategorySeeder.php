<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\ActivityCategory;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.11 - view all +
 * public, write Super Admin/Ketua/Sekretaris/Humas) and example categories.
 */
class ActivityCategorySeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'activity-categories.view',
            'activity-categories.create',
            'activity-categories.update',
            'activity-categories.delete',
            'activity-categories.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('activity-categories.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value, RoleName::Humas->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $names = ['Bakti Sosial', 'Kajian Rutin', 'Olahraga Bersama'];

        foreach ($names as $index => $name) {
            ActivityCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'display_order' => $index],
            );
        }
    }
}
