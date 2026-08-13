<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

/**
 * A position cannot become its own ancestor (BACKEND_ARCHITECTURE.md §4.8's
 * table, §20.1). Requires recursive traversal, so it cannot be a DB
 * constraint - enforced in OrganizationPositionService.
 */
class OrganizationPositionCycleException extends DomainException
{
}
