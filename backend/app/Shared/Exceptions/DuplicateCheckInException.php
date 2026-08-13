<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

/**
 * A member has already checked into this session
 * (DATABASE_SPECIFICATION.md §9 - uq_attendances_session_member).
 */
class DuplicateCheckInException extends DomainException
{
}
