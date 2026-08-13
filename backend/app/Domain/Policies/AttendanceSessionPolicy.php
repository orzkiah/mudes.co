<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\AttendanceSession;
use App\Domain\Models\User;

/**
 * PROJECT_SPECIFICATION.md §15 - create/manage sessions + manual entries:
 * Super Admin, Sekretaris. View reports: Super Admin, Ketua, Sekretaris.
 */
class AttendanceSessionPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('attendance-sessions.view');
    }

    public function view(User $user, ?AttendanceSession $session = null): bool
    {
        return $user->can('attendance-sessions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('attendance-sessions.create');
    }

    public function update(User $user, ?AttendanceSession $session = null): bool
    {
        return $user->can('attendance-sessions.update');
    }

    public function delete(User $user, ?AttendanceSession $session = null): bool
    {
        return $user->can('attendance-sessions.delete');
    }

    public function restore(User $user, ?AttendanceSession $session = null): bool
    {
        return $user->can('attendance-sessions.restore');
    }
}
