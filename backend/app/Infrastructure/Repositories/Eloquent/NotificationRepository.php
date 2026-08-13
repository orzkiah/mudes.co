<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Notification;
use App\Domain\Models\User;
use App\Infrastructure\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function paginateFor(User $user, int $perPage, array $filters, ?string $search): CursorPaginator
    {
        $query = $user->notifications();

        if (array_key_exists('isRead', $filters)) {
            $filters['isRead']
                ? $query->whereNotNull('read_at')
                : $query->whereNull('read_at');
        }

        if (array_key_exists('type', $filters)) {
            $query->where('type', 'like', '%'.$filters['type'].'%');
        }

        if ($search !== null) {
            $query->where('data', 'ILIKE', "%{$search}%");
        }

        return $query->cursorPaginate($perPage);
    }

    public function unreadCountFor(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function findOwnedByOrFail(User $user, string $id): Notification
    {
        /** @var Notification $notification */
        $notification = $user->notifications()->findOrFail($id);

        return $notification;
    }

    public function markAllReadFor(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
