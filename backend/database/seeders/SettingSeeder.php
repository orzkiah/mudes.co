<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use App\Domain\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds this module's permissions (API_SPECIFICATION.md §9.26 - Super Admin
 * only, per PROJECT_SPECIFICATION.md §5.1) and a handful of default settings
 * rows so the admin settings screen isn't empty on a fresh install.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['settings.view', 'settings.create', 'settings.update', 'settings.delete', 'settings.restore'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::where('name', RoleName::SuperAdmin->value)->first();
        $superAdmin?->givePermissionTo($permissions);

        $defaults = [
            ['key' => 'general.site_name', 'value' => 'Mudes.co', 'type' => 'string', 'group' => 'general', 'description' => 'Public site name.'],
            ['key' => 'seo.default_title', 'value' => 'Mudes.co - Pemuda Pemudi LDII Desa Condet', 'type' => 'string', 'group' => 'seo', 'description' => 'Default meta title for pages without their own.'],
            ['key' => 'mail.from_address', 'value' => 'noreply@mudes.co', 'type' => 'string', 'group' => 'mail', 'description' => 'Default "from" address for outgoing email.'],
        ];

        foreach ($defaults as $default) {
            Setting::firstOrCreate(['key' => $default['key']], $default);
        }
    }
}
