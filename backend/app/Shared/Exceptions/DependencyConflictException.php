<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

/**
 * A record cannot be deleted because other records still reference it -
 * the RESTRICT case (DATABASE_SPECIFICATION.md §9) surfaced as a 409,
 * uniform across every taxonomy resource (API_SPECIFICATION.md §8.2).
 */
class DependencyConflictException extends DomainException
{
}
