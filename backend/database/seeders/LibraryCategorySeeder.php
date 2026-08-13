<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\LibraryCategory;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds this module's permissions (PROJECT_SPECIFICATION.md §3.11 - write
 * Super Admin/Sekretaris/Multimedia/Editor) and the example categories
 * named in PROJECT_SPECIFICATION.md §16.
 */
class LibraryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'library-categories.view',
            'library-categories.create',
            'library-categories.update',
            'library-categories.delete',
            'library-categories.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('library-categories.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Sekretaris->value, RoleName::Multimedia->value, RoleName::Editor->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $names = ['Materi Kajian', 'Khutbah', 'Panduan Organisasi'];

        foreach ($names as $index => $name) {
            LibraryCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'display_order' => $index],
            );
        }
    }
}
