<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Announcement;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (PROJECT_SPECIFICATION.md §3.12 - write
 * Super Admin/Ketua/Sekretaris/Humas) and a sample announcement.
 */
class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['announcements.view', 'announcements.create', 'announcements.update', 'announcements.delete', 'announcements.restore'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo('announcements.view');
        }

        $writers = Role::whereIn('name', [RoleName::SuperAdmin->value, RoleName::Ketua->value, RoleName::Sekretaris->value, RoleName::Humas->value])->get();
        foreach ($writers as $role) {
            $role->givePermissionTo($permissions);
        }

        Announcement::firstOrCreate(
            ['title' => 'Selamat Datang di Mudes.co'],
            [
                'body' => 'Platform digital resmi Pemuda Pemudi LDII Desa Condet kini telah hadir.',
                'priority' => 'normal',
                'audience' => 'public',
                'pinned' => true,
                'starts_at' => now(),
            ],
        );
    }
}
