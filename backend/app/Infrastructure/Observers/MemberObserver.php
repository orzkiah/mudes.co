<?php

declare(strict_types=1);

namespace App\Infrastructure\Observers;

use App\Domain\Models\Member;
use Illuminate\Support\Facades\Auth;

/**
 * Audit column population only - no business decisions
 * (IMPLEMENTATION_RULES.md §13).
 */
class MemberObserver
{
    public function creating(Member $member): void
    {
        $member->created_by = Auth::id();
        $member->updated_by = Auth::id();
    }

    public function updating(Member $member): void
    {
        $member->updated_by = Auth::id();
    }

    public function deleting(Member $member): void
    {
        $member->deleted_by = Auth::id();
        $member->saveQuietly();
    }
}
