<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * DATABASE_SPECIFICATION.md §5 - attendance_sessions.source_type.
 */
enum AttendanceSessionSourceType: string
{
    case ScheduleOccurrence = 'schedule_occurrence';
    case Activity = 'activity';
}
