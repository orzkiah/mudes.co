<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\Setting;
use App\Domain\Models\User;

/**
 * PROJECT_SPECIFICATION.md §5.1 - Website Settings is Super Admin only.
 * Enforced via permissions (settings.view/create/update/delete/restore),
 * seeded and assigned to super-admin in SettingSeeder, not a hardcoded role
 * check (BACKEND_ARCHITECTURE.md §11 - one enforcement path for every role).
 * No per-row logic exists, so the model parameter is optional throughout.
 */
class SettingPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.view');
    }

    public function view(User $user, ?Setting $setting = null): bool
    {
        return $user->can('settings.view');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.create');
    }

    public function update(User $user, ?Setting $setting = null): bool
    {
        return $user->can('settings.update');
    }

    public function delete(User $user, ?Setting $setting = null): bool
    {
        return $user->can('settings.delete');
    }

    public function restore(User $user, ?Setting $setting = null): bool
    {
        return $user->can('settings.restore');
    }
}
