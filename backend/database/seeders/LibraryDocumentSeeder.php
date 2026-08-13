<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\LibraryCategory;
use App\Domain\Models\LibraryDocument;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (PROJECT_SPECIFICATION.md §3.11 - view
 * internal + write restricted to Super
 * Admin/Sekretaris/Multimedia/Editor) and a sample public document.
 */
class LibraryDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'library-documents.view',
            'library-documents.create',
            'library-documents.update',
            'library-documents.delete',
            'library-documents.restore',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = Role::whereIn('name', [
            RoleName::SuperAdmin->value,
            RoleName::Sekretaris->value,
            RoleName::Multimedia->value,
            RoleName::Editor->value,
        ])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }

        $category = LibraryCategory::where('name', 'Panduan Organisasi')->first();

        if ($category !== null) {
            LibraryDocument::firstOrCreate(
                ['title' => 'Panduan Keanggotaan Mudes'],
                [
                    'library_category_id' => $category->id,
                    'description' => 'Panduan dasar keanggotaan Pemuda Pemudi LDII Desa Condet.',
                    'external_url' => 'https://example.com/panduan-keanggotaan',
                    'visibility' => 'public',
                ],
            );
        }
    }
}
