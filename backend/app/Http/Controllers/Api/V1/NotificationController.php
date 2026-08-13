<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\NotificationService;
use App\Domain\Models\User;
use App\Http\Resources\NotificationResource;
use App\Shared\Support\CursorPaginationMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function __construct(private readonly NotificationService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $filters = array_filter([
            'isRead' => $request->has('filter.isRead') ? $request->boolean('filter.isRead') : null,
            'type' => $request->query('filter.type'),
        ], fn ($value) => $value !== null);

        $search = $request->query('search');
        $perPage = min((int) $request->query('perPage', 20), 100);

        $paginator = $this->service->paginateFor($user, $perPage, $filters, is_string($search) ? $search : null);

        return $this->success(
            data: NotificationResource::collection($paginator)->resolve(),
            message: 'Notifications retrieved successfully.',
            meta: CursorPaginationMeta::fromPaginator($paginator),
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(['count' => $this->service->unreadCountFor($user)], 'Unread count retrieved successfully.');
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updated = $this->service->markRead($user, $notification);

        return $this->success(new NotificationResource($updated), 'Notification marked as read successfully.');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $count = $this->service->markAllRead($user);

        return $this->success(['updatedCount' => $count], 'All notifications marked as read successfully.');
    }

    public function destroy(Request $request, string $notification): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->service->delete($user, $notification);

        return $this->success(['id' => $notification], 'Notification deleted successfully.');
    }
}
