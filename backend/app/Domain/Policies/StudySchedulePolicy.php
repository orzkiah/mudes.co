<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\StudySchedule;
use App\Domain\Models\User;

/**
 * API_SPECIFICATION.md §9.10, PROJECT_SPECIFICATION.md §3.7 - view all +
 * public, write Super Admin/Ketua/Sekretaris.
 */
class StudySchedulePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('study-schedules.view');
    }

    public function view(User $user, ?StudySchedule $schedule = null): bool
    {
        return $user->can('study-schedules.view');
    }

    public function create(User $user): bool
    {
        return $user->can('study-schedules.create');
    }

    public function update(User $user, ?StudySchedule $schedule = null): bool
    {
        return $user->can('study-schedules.update');
    }

    public function delete(User $user, ?StudySchedule $schedule = null): bool
    {
        return $user->can('study-schedules.delete');
    }

    public function restore(User $user, ?StudySchedule $schedule = null): bool
    {
        return $user->can('study-schedules.restore');
    }
}
