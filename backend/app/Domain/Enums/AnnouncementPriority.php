<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * DATABASE_SPECIFICATION.md §5 - announcements.priority.
 */
enum AnnouncementPriority: string
{
    case Normal = 'normal';
    case Urgent = 'urgent';
}
