<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\ArticleCategory;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.14 - write
 * Super Admin/Ketua/Humas) and example categories.
 */
class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'article-categories.view',
            'article-categories.create',
            'article-categories.update',
            'article-categories.delete',
            'article-categories.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('article-categories.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Humas->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $names = ['Berita', 'Artikel Keislaman', 'Pengumuman'];

        foreach ($names as $index => $name) {
            ArticleCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'display_order' => $index],
            );
        }
    }
}
