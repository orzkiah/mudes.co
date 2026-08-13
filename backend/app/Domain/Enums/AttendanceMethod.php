<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * DATABASE_SPECIFICATION.md §5 - attendances.method.
 */
enum AttendanceMethod: string
{
    case Qr = 'qr';
    case Manual = 'manual';
}
