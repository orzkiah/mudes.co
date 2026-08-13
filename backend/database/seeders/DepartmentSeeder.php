<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Department;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (PROJECT_SPECIFICATION.md §5.1 - write
 * access for Super Admin, Ketua, Sekretaris) and a couple of example
 * departments so the admin screen isn't empty on a fresh install.
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['departments.view', 'departments.create', 'departments.update', 'departments.delete', 'departments.restore'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $readOnlyRoles = Role::whereNotIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value])->get();
        foreach ($readOnlyRoles as $role) {
            $role->givePermissionTo(['departments.view']);
        }

        $defaults = [
            ['name' => 'Bidang Humas', 'slug' => 'bidang-humas', 'icon' => 'megaphone', 'color' => '#4F46E5', 'display_order' => 1],
            ['name' => 'Bidang Multimedia', 'slug' => 'bidang-multimedia', 'icon' => 'camera', 'color' => '#059669', 'display_order' => 2],
        ];

        foreach ($defaults as $default) {
            Department::firstOrCreate(['name' => $default['name']], $default);
        }
    }
}
