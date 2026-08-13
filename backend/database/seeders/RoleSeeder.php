<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the six fixed roles (PROJECT_SPECIFICATION.md §5). Permission
 * assignment per role is added module by module, as each module's
 * permissions are defined - none exist yet.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleName::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
    }
}
