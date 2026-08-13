<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the dashboard.view permission (PROJECT_SPECIFICATION.md §17 -
 * reports viewable by Super Admin/Ketua/Sekretaris, mirroring Attendance's
 * "view reports" audience).
 */
class DashboardAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);

        $roles = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo('dashboard.view');
        }
    }
}
