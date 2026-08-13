<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * DATABASE_SPECIFICATION.md §5 - announcements.audience.
 */
enum AnnouncementAudience: string
{
    case PublicAudience = 'public';
    case Internal = 'internal';
}
