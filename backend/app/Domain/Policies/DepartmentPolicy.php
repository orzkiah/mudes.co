<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\Department;
use App\Domain\Models\User;

/**
 * PROJECT_SPECIFICATION.md §5.1 - Departments: view all + public; write
 * Super Admin, Ketua, Sekretaris (same as Organization Structure).
 * Permission-based, not a hardcoded role check (BACKEND_ARCHITECTURE.md §11).
 */
class DepartmentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('departments.view');
    }

    public function view(User $user, ?Department $department = null): bool
    {
        return $user->can('departments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('departments.create');
    }

    public function update(User $user, ?Department $department = null): bool
    {
        return $user->can('departments.update');
    }

    public function delete(User $user, ?Department $department = null): bool
    {
        return $user->can('departments.delete');
    }

    public function restore(User $user, ?Department $department = null): bool
    {
        return $user->can('departments.restore');
    }
}
