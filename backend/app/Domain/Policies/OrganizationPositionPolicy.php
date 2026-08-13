<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\OrganizationPosition;
use App\Domain\Models\User;

/**
 * API_SPECIFICATION.md §9.8 - view all + public (active period); write
 * Super Admin, Ketua, Sekretaris. Enforced via permissions, not hardcoded
 * role checks (BACKEND_ARCHITECTURE.md §11).
 */
class OrganizationPositionPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('organization-positions.view');
    }

    public function view(User $user, ?OrganizationPosition $position = null): bool
    {
        return $user->can('organization-positions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('organization-positions.create');
    }

    public function update(User $user, ?OrganizationPosition $position = null): bool
    {
        return $user->can('organization-positions.update');
    }

    public function delete(User $user, ?OrganizationPosition $position = null): bool
    {
        return $user->can('organization-positions.delete');
    }

    public function restore(User $user, ?OrganizationPosition $position = null): bool
    {
        return $user->can('organization-positions.restore');
    }
}
