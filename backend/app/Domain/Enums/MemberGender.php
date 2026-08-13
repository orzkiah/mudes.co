<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * DATABASE_SPECIFICATION.md §4.2 / §11 - members.gender.
 */
enum MemberGender: string
{
    case Male = 'male';
    case Female = 'female';
}
