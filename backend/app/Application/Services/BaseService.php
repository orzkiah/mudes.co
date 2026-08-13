<?php

declare(strict_types=1);

namespace App\Application\Services;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Concrete Services own business logic and coordinate one or more
 * Repositories via their interfaces (BACKEND_ARCHITECTURE.md §5). This base
 * class only provides the one thing every mutating Service needs: a
 * transaction wrapper (IMPLEMENTATION_RULES.md §5).
 */
abstract class BaseService
{
    /**
     * Wrap a mutating operation in a database transaction.
     */
    protected function transaction(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
