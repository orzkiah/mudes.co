<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use App\Domain\Models\StudyCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.9 - view all +
 * public, write Super Admin/Ketua/Sekretaris) and the example categories
 * named in the spec's seed note.
 */
class StudyCategorySeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'study-categories.view',
            'study-categories.create',
            'study-categories.update',
            'study-categories.delete',
            'study-categories.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('study-categories.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $names = ['Weekly Study', 'Monthly Study', 'Youth Study', 'Special Study', 'Ramadan', 'National Event'];

        foreach ($names as $index => $name) {
            StudyCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'display_order' => $index],
            );
        }
    }
}
