<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * DATABASE_SPECIFICATION.md §4.8 / §5 - organization_positions.position_type.
 */
enum OrganizationPositionType: string
{
    case Chairman = 'chairman';
    case ViceChairman = 'vice_chairman';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Coordinator = 'coordinator';
    case Member = 'member';
}
