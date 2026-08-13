<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared permission-based authorization for every Taxonomy Resource
 * Contract module. Concrete Policies only implement permissionPrefix()
 * (BACKEND_ARCHITECTURE.md §11 - one enforcement path, no hardcoded role
 * checks).
 */
abstract class AbstractTaxonomyPolicy extends BasePolicy
{
    abstract protected function permissionPrefix(): string;

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionPrefix().'.view');
    }

    public function view(User $user, ?Model $model = null): bool
    {
        return $user->can($this->permissionPrefix().'.view');
    }

    public function create(User $user): bool
    {
        return $user->can($this->permissionPrefix().'.create');
    }

    public function update(User $user, ?Model $model = null): bool
    {
        return $user->can($this->permissionPrefix().'.update');
    }

    public function delete(User $user, ?Model $model = null): bool
    {
        return $user->can($this->permissionPrefix().'.delete');
    }

    public function restore(User $user, ?Model $model = null): bool
    {
        return $user->can($this->permissionPrefix().'.restore');
    }
}
