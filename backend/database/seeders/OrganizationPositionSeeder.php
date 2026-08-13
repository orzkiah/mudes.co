<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Department;
use App\Domain\Models\Member;
use App\Domain\Models\OrganizationPeriod;
use App\Domain\Models\OrganizationPosition;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.8 - view all,
 * write Super Admin/Ketua/Sekretaris), a default active period (the minimal
 * `organization_periods` prerequisite - see its migration note), and a small
 * sample hierarchy so the admin org-chart screen isn't empty on install.
 */
class OrganizationPositionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'organization-positions.view',
            'organization-positions.create',
            'organization-positions.update',
            'organization-positions.delete',
            'organization-positions.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('organization-positions.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        // Organization Periods — same permission matrix as Positions
        // (API_SPECIFICATION.md §9.8: view all; write Super Admin, Ketua, Sekretaris).
        $periodPermissions = [
            'organization-periods.view',
            'organization-periods.create',
            'organization-periods.update',
            'organization-periods.delete',
            'organization-periods.restore',
        ];

        foreach ($periodPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('organization-periods.view');
        }

        $periodWriters = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value])->get();
        foreach ($periodWriters as $role) {
            $role->givePermissionTo($periodPermissions);
        }

        $period = OrganizationPeriod::firstOrCreate(
            ['label' => 'Periode 2023-2025'],
            ['start_date' => '2023-01-01', 'end_date' => '2025-12-31', 'is_active' => true],
        );

        $humas = Department::where('name', 'Bidang Humas')->first();
        $ahmad = Member::where('full_name', 'Ahmad Fauzi')->first();
        $siti = Member::where('full_name', 'Siti Nurhaliza')->first();

        $chairman = OrganizationPosition::firstOrCreate(
            ['organization_period_id' => $period->id, 'title' => 'Ketua'],
            ['position_type' => 'chairman', 'level' => 0, 'display_order' => 0, 'member_id' => $ahmad?->id],
        );

        OrganizationPosition::firstOrCreate(
            ['organization_period_id' => $period->id, 'title' => 'Sekretaris'],
            ['position_type' => 'secretary', 'parent_position_id' => $chairman->id, 'level' => 1, 'display_order' => 0, 'member_id' => $siti?->id],
        );

        OrganizationPosition::firstOrCreate(
            ['organization_period_id' => $period->id, 'title' => 'Koordinator Bidang Humas'],
            ['position_type' => 'coordinator', 'parent_position_id' => $chairman->id, 'department_id' => $humas?->id, 'level' => 1, 'display_order' => 1],
        );
    }
}
