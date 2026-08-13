<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * DATABASE_SPECIFICATION.md §4.2 / §11 - members.status, replaces the old
 * is_active boolean. `alumni`/`moved_out` members stay queryable in history
 * but are excluded from "current members" listings by default.
 */
enum MemberStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Alumni = 'alumni';
    case MovedOut = 'moved_out';
}
