<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\Notification;
use App\Domain\Models\User;
use App\Infrastructure\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;

class NotificationService
{
    public function __construct(private readonly NotificationRepositoryInterface $repository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateFor(User $user, int $perPage, array $filters, ?string $search): CursorPaginator
    {
        return $this->repository->paginateFor($user, $perPage, $filters, $search);
    }

    public function unreadCountFor(User $user): int
    {
        return $this->repository->unreadCountFor($user);
    }

    public function markRead(User $user, string $id): Notification
    {
        $notification = $this->repository->findOwnedByOrFail($user, $id);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return $notification->refresh();
    }

    public function markAllRead(User $user): int
    {
        return $this->repository->markAllReadFor($user);
    }

    public function delete(User $user, string $id): void
    {
        $notification = $this->repository->findOwnedByOrFail($user, $id);
        $notification->delete();
    }
}
