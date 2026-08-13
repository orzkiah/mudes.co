<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Every business Model gets a Policy extending this
 * (IMPLEMENTATION_RULES.md §10). Kept intentionally empty beyond the
 * standard authorization helpers - a role-bypass hook (e.g. Super Admin)
 * is deferred until Spatie Permission is actually wired up with real
 * roles (Step 9), rather than guessed at now.
 */
abstract class BasePolicy
{
    use HandlesAuthorization;
}
