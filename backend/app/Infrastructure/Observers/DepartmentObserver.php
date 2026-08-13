<?php

declare(strict_types=1);

namespace App\Infrastructure\Observers;

use App\Domain\Models\Department;
use Illuminate\Support\Facades\Auth;

/**
 * Audit column population only - no business decisions
 * (IMPLEMENTATION_RULES.md §13).
 */
class DepartmentObserver
{
    public function creating(Department $department): void
    {
        $department->created_by = Auth::id();
        $department->updated_by = Auth::id();
    }

    public function updating(Department $department): void
    {
        $department->updated_by = Auth::id();
    }

    public function deleting(Department $department): void
    {
        $department->deleted_by = Auth::id();
        $department->saveQuietly();
    }
}
