<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Notification;
use App\Domain\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;

/**
 * Every method here is implicitly scoped to one caller
 * (API_SPECIFICATION.md §9.21 - "self-scoped only") - there is no
 * cross-user notification listing.
 */
interface NotificationRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateFor(User $user, int $perPage, array $filters, ?string $search): CursorPaginator;

    public function unreadCountFor(User $user): int;

    public function findOwnedByOrFail(User $user, string $id): Notification;

    public function markAllReadFor(User $user): int;
}
