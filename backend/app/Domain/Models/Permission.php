<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Shared\Traits\HasUuid;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Extends Spatie's Permission model only to attach UUID generation
 * (DATABASE_SPECIFICATION.md §4.3) - wired here now that HasUuid exists,
 * closing the deferral noted in Step 2.
 */
class Permission extends SpatiePermission
{
    use HasUuid;
}
