<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * The six fixed roles (PROJECT_SPECIFICATION.md §5). Roles are not
 * creatable/deletable via the API - this enum is the single source of
 * truth for the seeded set (API_SPECIFICATION.md §9.3).
 */
enum RoleName: string
{
    case SuperAdmin = 'super-admin';
    case Ketua = 'ketua';
    case Sekretaris = 'sekretaris';
    case Humas = 'humas';
    case Multimedia = 'multimedia';
    case Editor = 'editor';
}
