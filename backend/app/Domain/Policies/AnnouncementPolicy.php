<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\Announcement;
use App\Domain\Models\User;

/**
 * PROJECT_SPECIFICATION.md §3.12 - read audience-scoped (enforced by public
 * route only ever querying audience=public + not-expired); write Super
 * Admin/Ketua/Sekretaris/Humas.
 */
class AnnouncementPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('announcements.view');
    }

    public function view(User $user, ?Announcement $announcement = null): bool
    {
        return $user->can('announcements.view');
    }

    public function create(User $user): bool
    {
        return $user->can('announcements.create');
    }

    public function update(User $user, ?Announcement $announcement = null): bool
    {
        return $user->can('announcements.update');
    }

    public function delete(User $user, ?Announcement $announcement = null): bool
    {
        return $user->can('announcements.delete');
    }

    public function restore(User $user, ?Announcement $announcement = null): bool
    {
        return $user->can('announcements.restore');
    }
}
