<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use RuntimeException;

/**
 * Base class for business-rule failures thrown by Services
 * (IMPLEMENTATION_RULES.md §16) - never a bare \Exception.
 */
abstract class DomainException extends RuntimeException
{
}
