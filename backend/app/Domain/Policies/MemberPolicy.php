<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\Member;
use App\Domain\Models\User;

/**
 * API_SPECIFICATION.md §9.5 - members.view (Sekretaris, Super Admin, Ketua),
 * members.create/update/delete/restore (Sekretaris, Super Admin). Enforced
 * via permissions, not hardcoded role checks (BACKEND_ARCHITECTURE.md §11).
 */
class MemberPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('members.view');
    }

    public function view(User $user, ?Member $member = null): bool
    {
        return $user->can('members.view');
    }

    public function create(User $user): bool
    {
        return $user->can('members.create');
    }

    public function update(User $user, ?Member $member = null): bool
    {
        return $user->can('members.update');
    }

    public function delete(User $user, ?Member $member = null): bool
    {
        return $user->can('members.delete');
    }

    public function restore(User $user, ?Member $member = null): bool
    {
        return $user->can('members.restore');
    }
}
