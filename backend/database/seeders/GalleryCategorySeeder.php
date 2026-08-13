<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\GalleryCategory;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.16 - view all +
 * public, write Multimedia/Super Admin) and example categories.
 */
class GalleryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'gallery-categories.view',
            'gallery-categories.create',
            'gallery-categories.update',
            'gallery-categories.delete',
            'gallery-categories.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('gallery-categories.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Multimedia->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $names = ['Dokumentasi Kegiatan', 'Bakti Sosial', 'Kajian'];

        foreach ($names as $index => $name) {
            GalleryCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'display_order' => $index],
            );
        }
    }
}
