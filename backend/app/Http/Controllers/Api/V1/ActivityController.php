<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateActivityDTO;
use App\Application\DTO\UpdateActivityDTO;
use App\Application\Services\ActivityService;
use App\Domain\Models\Activity;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Shared\Support\CursorPaginationMeta;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
    public function __construct(private readonly ActivityService $service)
    {
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['activity_category_id', 'status'],
            searchableColumns: ['title'],
        );

        $query = Activity::query()->with(['category', 'cover']);
        $filter->apply($query);

        $paginator = $query->cursorPaginate(min((int) $request->query('perPage', 20), 100));

        return $this->success(
            data: ActivityResource::collection($paginator)->resolve(),
            message: 'Activities retrieved successfully.',
            meta: CursorPaginationMeta::fromPaginator($paginator),
        );
    }

    public function publicShow(string $slug): JsonResponse
    {
        $model = $this->service->findBySlug($slug);

        abort_if($model === null, 404);

        return $this->success(new ActivityResource($model), 'Activity retrieved successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['activity_category_id', 'status'],
            allowedSorts: ['start_at', 'created_at'],
            searchableColumns: ['title'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: ActivityResource::collection($paginator)->resolve(),
            message: 'Activities retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $activity): JsonResponse
    {
        $model = $this->service->find($activity);

        $this->authorize('view', $model);

        return $this->success(new ActivityResource($model), 'Activity retrieved successfully.');
    }

    public function store(StoreActivityRequest $request): JsonResponse
    {
        $dto = CreateActivityDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new ActivityResource($model), 'Activity created successfully.', status: 201);
    }

    public function update(UpdateActivityRequest $request, string $activity): JsonResponse
    {
        $model = $this->service->find($activity);

        $dto = UpdateActivityDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new ActivityResource($updated), 'Activity updated successfully.');
    }

    public function destroy(string $activity): JsonResponse
    {
        $model = $this->service->find($activity);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Activity deleted successfully.');
    }

    public function restore(string $activity): JsonResponse
    {
        $this->authorize('restore', Activity::class);

        $restored = $this->service->restore($activity);

        return $this->success(new ActivityResource($restored), 'Activity restored successfully.');
    }
}
