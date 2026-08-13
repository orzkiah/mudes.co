<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (PROJECT_SPECIFICATION.md §15 - manage:
 * Super Admin/Sekretaris; view reports: Super Admin/Ketua/Sekretaris).
 */
class AttendanceSessionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'attendance-sessions.view',
            'attendance-sessions.create',
            'attendance-sessions.update',
            'attendance-sessions.delete',
            'attendance-sessions.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $viewers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value])->get();
        foreach ($viewers as $role) {
            $role->givePermissionTo('attendance-sessions.view');
        }

        $managers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Sekretaris->value])->get();
        foreach ($managers as $role) {
            $role->givePermissionTo(['attendance-sessions.create', 'attendance-sessions.update', 'attendance-sessions.delete', 'attendance-sessions.restore']);
        }
    }
}
