<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Shared\Traits\HasUuid;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Extends Spatie's Role model only to attach UUID generation
 * (DATABASE_SPECIFICATION.md §4.3) - wired here now that HasUuid exists,
 * closing the deferral noted in Step 2.
 */
class Role extends SpatieRole
{
    use HasUuid;
}
