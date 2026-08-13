<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\Activity;
use App\Domain\Models\User;

/**
 * API_SPECIFICATION.md §9.13, PROJECT_SPECIFICATION.md §3.8 - view all +
 * public, write Super Admin/Ketua/Sekretaris/Humas.
 */
class ActivityPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('activities.view');
    }

    public function view(User $user, ?Activity $activity = null): bool
    {
        return $user->can('activities.view');
    }

    public function create(User $user): bool
    {
        return $user->can('activities.create');
    }

    public function update(User $user, ?Activity $activity = null): bool
    {
        return $user->can('activities.update');
    }

    public function delete(User $user, ?Activity $activity = null): bool
    {
        return $user->can('activities.delete');
    }

    public function restore(User $user, ?Activity $activity = null): bool
    {
        return $user->can('activities.restore');
    }
}
