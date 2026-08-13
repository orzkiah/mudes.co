<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Article;
use App\Domain\Models\ArticleCategory;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds this module's permissions (PROJECT_SPECIFICATION.md §3.9 - view all
 * + public published-only, write Editor/Humas/Super Admin/Ketua) and a
 * sample published article.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['articles.view', 'articles.create', 'articles.update', 'articles.delete', 'articles.restore'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('articles.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Humas->value, RoleName::Editor->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        $category = ArticleCategory::where('name', 'Berita')->first();
        $title = 'Selamat Datang di Mudes.co';

        if ($category !== null) {
            Article::firstOrCreate(
                ['title' => $title],
                [
                    'article_category_id' => $category->id,
                    'slug' => Str::slug($title),
                    'excerpt' => 'Platform digital resmi Pemuda Pemudi LDII Desa Condet.',
                    'body' => 'Selamat datang di platform digital resmi Pemuda Pemudi LDII Desa Condet.',
                    'status' => 'published',
                    'published_at' => now(),
                ],
            );
        }
    }
}
