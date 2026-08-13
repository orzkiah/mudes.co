<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\Gallery;
use App\Domain\Models\User;

/**
 * PROJECT_SPECIFICATION.md §3.10 - view all + public, write
 * Multimedia/Super Admin.
 */
class GalleryPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('galleries.view');
    }

    public function view(User $user, ?Gallery $gallery = null): bool
    {
        return $user->can('galleries.view');
    }

    public function create(User $user): bool
    {
        return $user->can('galleries.create');
    }

    public function update(User $user, ?Gallery $gallery = null): bool
    {
        return $user->can('galleries.update');
    }

    public function delete(User $user, ?Gallery $gallery = null): bool
    {
        return $user->can('galleries.delete');
    }

    public function restore(User $user, ?Gallery $gallery = null): bool
    {
        return $user->can('galleries.restore');
    }
}
