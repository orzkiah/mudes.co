<?php

declare(strict_types=1);

namespace App\Infrastructure\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Audit column population only - no business decisions
 * (IMPLEMENTATION_RULES.md §13). Generic across every model with
 * created_by/updated_by/deleted_by columns, used via
 * #[ObservedBy(AuditObserver::class)] on every module built from this point
 * on - the per-module Observer classes built for earlier modules
 * (Setting/Department/Member/OrganizationPosition) predate this and are left
 * as-is rather than retrofitted.
 */
class AuditObserver
{
    public function creating(Model $model): void
    {
        $model->setAttribute('created_by', Auth::id());
        $model->setAttribute('updated_by', Auth::id());
    }

    public function updating(Model $model): void
    {
        $model->setAttribute('updated_by', Auth::id());
    }

    public function deleting(Model $model): void
    {
        $model->setAttribute('deleted_by', Auth::id());
        $model->saveQuietly();
    }
}
