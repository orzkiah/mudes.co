<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Member;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.5 - view for
 * Sekretaris/Super Admin/Ketua, write for Sekretaris/Super Admin) and a
 * couple of example members so the admin screen isn't empty on a fresh
 * install.
 */
class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['members.view', 'members.create', 'members.update', 'members.delete', 'members.restore'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Sekretaris->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $ketua = Role::where('name', RoleName::Ketua->value)->first();
        $ketua?->givePermissionTo('members.view');

        $defaults = [
            ['full_name' => 'Ahmad Fauzi', 'gender' => 'male', 'join_date' => '2023-01-15', 'status' => 'active'],
            ['full_name' => 'Siti Nurhaliza', 'gender' => 'female', 'join_date' => '2023-03-10', 'status' => 'active'],
        ];

        foreach ($defaults as $default) {
            Member::firstOrCreate(['full_name' => $default['full_name']], $default);
        }
    }
}
