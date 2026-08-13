<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

/**
 * The session's qr_token was scanned outside its opens_at-closes_at window
 * (BACKEND_ARCHITECTURE.md §20.1, PROJECT_SPECIFICATION.md §15).
 */
class AttendanceWindowClosedException extends DomainException
{
}
