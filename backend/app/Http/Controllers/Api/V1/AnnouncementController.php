<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateAnnouncementDTO;
use App\Application\DTO\UpdateAnnouncementDTO;
use App\Application\Services\AnnouncementService;
use App\Domain\Models\Announcement;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Shared\Support\CursorPaginationMeta;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends BaseController
{
    public function __construct(private readonly AnnouncementService $service)
    {
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $query = Announcement::query()
            ->where('audience', 'public')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->orderByDesc('pinned')
            ->orderByDesc('starts_at');

        $paginator = $query->cursorPaginate(min((int) $request->query('perPage', 20), 100));

        return $this->success(
            data: AnnouncementResource::collection($paginator)->resolve(),
            message: 'Announcements retrieved successfully.',
            meta: CursorPaginationMeta::fromPaginator($paginator),
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Announcement::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['audience', 'priority', 'pinned'],
            allowedSorts: ['starts_at', 'created_at'],
            searchableColumns: ['title'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: AnnouncementResource::collection($paginator)->resolve(),
            message: 'Announcements retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $announcement): JsonResponse
    {
        $model = $this->service->find($announcement);

        $this->authorize('view', $model);

        return $this->success(new AnnouncementResource($model), 'Announcement retrieved successfully.');
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $dto = CreateAnnouncementDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new AnnouncementResource($model), 'Announcement created successfully.', status: 201);
    }

    public function update(UpdateAnnouncementRequest $request, string $announcement): JsonResponse
    {
        $model = $this->service->find($announcement);

        $dto = UpdateAnnouncementDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new AnnouncementResource($updated), 'Announcement updated successfully.');
    }

    public function destroy(string $announcement): JsonResponse
    {
        $model = $this->service->find($announcement);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Announcement deleted successfully.');
    }

    public function restore(string $announcement): JsonResponse
    {
        $this->authorize('restore', Announcement::class);

        $restored = $this->service->restore($announcement);

        return $this->success(new AnnouncementResource($restored), 'Announcement restored successfully.');
    }
}
