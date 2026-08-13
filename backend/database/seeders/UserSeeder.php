<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\RoleName;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the one bootstrap account every fresh environment needs to log
 * into the Admin Dashboard for the first time (DATABASE_SPECIFICATION.md
 * §4.1) - not the Users business module, which stays deferred.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@mudes.co'],
            [
                'name' => 'Super Admin',
                'password' => 'Password123',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        if (! $user->hasRole(RoleName::SuperAdmin->value)) {
            $user->assignRole(RoleName::SuperAdmin->value);
        }
    }
}
