<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\OrganizationPeriod;
use App\Domain\Models\User;

/**
 * API_SPECIFICATION.md §9.8 — same permission matrix as Organization
 * Positions: view all; write Super Admin, Ketua, Sekretaris.
 * Permission-based enforcement (BACKEND_ARCHITECTURE.md §11).
 */
class OrganizationPeriodPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('organization-periods.view');
    }

    public function view(User $user, ?OrganizationPeriod $period = null): bool
    {
        return $user->can('organization-periods.view');
    }

    public function create(User $user): bool
    {
        return $user->can('organization-periods.create');
    }

    public function update(User $user, ?OrganizationPeriod $period = null): bool
    {
        return $user->can('organization-periods.update');
    }

    public function delete(User $user, ?OrganizationPeriod $period = null): bool
    {
        return $user->can('organization-periods.delete');
    }

    public function restore(User $user, ?OrganizationPeriod $period = null): bool
    {
        return $user->can('organization-periods.restore');
    }

    /**
     * Activating a period is a write operation — requires update permission.
     */
    public function activate(User $user, ?OrganizationPeriod $period = null): bool
    {
        return $user->can('organization-periods.update');
    }
}
