<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Gallery;
use App\Domain\Models\GalleryCategory;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (PROJECT_SPECIFICATION.md §3.10 - view
 * all + public, write Multimedia/Super Admin) and a sample album.
 */
class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['galleries.view', 'galleries.create', 'galleries.update', 'galleries.delete', 'galleries.restore'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('galleries.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Multimedia->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $category = GalleryCategory::where('name', 'Dokumentasi Kegiatan')->first();

        if ($category !== null) {
            Gallery::firstOrCreate(
                ['title' => 'Dokumentasi Kajian Rutin'],
                ['gallery_category_id' => $category->id, 'description' => 'Dokumentasi foto kegiatan kajian rutin mingguan.'],
            );
        }
    }
}
