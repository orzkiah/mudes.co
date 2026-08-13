<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\PartialTaxonomyDTO;
use App\Application\DTO\TaxonomyDTO;
use App\Application\Services\ActivityCategoryService;
use App\Domain\Models\ActivityCategory;
use App\Http\Requests\BulkTaxonomyActionRequest;
use App\Http\Requests\ReorderActivityCategoryRequest;
use App\Http\Requests\StoreActivityCategoryRequest;
use App\Http\Requests\UpdateActivityCategoryRequest;
use App\Http\Resources\ActivityCategoryResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityCategoryController extends BaseController
{
    public function __construct(private readonly ActivityCategoryService $service)
    {
    }

    public function publicIndex(): JsonResponse
    {
        return $this->success(
            ActivityCategoryResource::collection($this->service->listActive())->resolve(),
            'Activity categories retrieved successfully.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ActivityCategory::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['is_active'],
            allowedSorts: ['display_order', 'name', 'created_at'],
            searchableColumns: ['name'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: ActivityCategoryResource::collection($paginator)->resolve(),
            message: 'Activity categories retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $activityCategory): JsonResponse
    {
        $model = $this->service->find($activityCategory);

        $this->authorize('view', $model);

        return $this->success(new ActivityCategoryResource($model), 'Activity category retrieved successfully.');
    }

    public function store(StoreActivityCategoryRequest $request): JsonResponse
    {
        $dto = TaxonomyDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new ActivityCategoryResource($model), 'Activity category created successfully.', status: 201);
    }

    public function update(UpdateActivityCategoryRequest $request, string $activityCategory): JsonResponse
    {
        $model = $this->service->find($activityCategory);

        $dto = PartialTaxonomyDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new ActivityCategoryResource($updated), 'Activity category updated successfully.');
    }

    public function destroy(string $activityCategory): JsonResponse
    {
        $model = $this->service->find($activityCategory);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Activity category deleted successfully.');
    }

    public function restore(string $activityCategory): JsonResponse
    {
        $this->authorize('restore', ActivityCategory::class);

        $restored = $this->service->restore($activityCategory);

        return $this->success(new ActivityCategoryResource($restored), 'Activity category restored successfully.');
    }

    public function bulkActivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', ActivityCategory::class);

        $count = $this->service->bulkActivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Activity categories activated successfully.');
    }

    public function bulkDeactivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', ActivityCategory::class);

        $count = $this->service->bulkDeactivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Activity categories deactivated successfully.');
    }

    public function bulkDelete(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('delete', ActivityCategory::class);

        $count = $this->service->bulkDelete($request->validated('ids'));

        return $this->success(['deleted' => $count], 'Activity categories deleted successfully.');
    }

    public function bulkRestore(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('restore', ActivityCategory::class);

        $count = $this->service->bulkRestore($request->validated('ids'));

        return $this->success(['restored' => $count], 'Activity categories restored successfully.');
    }

    public function reorder(ReorderActivityCategoryRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('order'));

        return $this->success(null, 'Activity categories reordered successfully.');
    }
}
