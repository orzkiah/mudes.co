<?php

declare(strict_types=1);

namespace App\Infrastructure\Observers;

use App\Domain\Models\OrganizationPosition;
use Illuminate\Support\Facades\Auth;

/**
 * Audit column population only - no business decisions
 * (IMPLEMENTATION_RULES.md §13). Level/cycle logic lives in
 * OrganizationPositionService, not here.
 */
class OrganizationPositionObserver
{
    public function creating(OrganizationPosition $position): void
    {
        $position->created_by = Auth::id();
        $position->updated_by = Auth::id();
    }

    public function updating(OrganizationPosition $position): void
    {
        $position->updated_by = Auth::id();
    }

    public function deleting(OrganizationPosition $position): void
    {
        $position->deleted_by = Auth::id();
        $position->saveQuietly();
    }
}
