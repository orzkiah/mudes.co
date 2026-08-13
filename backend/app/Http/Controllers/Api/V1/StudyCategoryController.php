<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\PartialTaxonomyDTO;
use App\Application\DTO\TaxonomyDTO;
use App\Application\Services\StudyCategoryService;
use App\Domain\Models\StudyCategory;
use App\Http\Requests\BulkTaxonomyActionRequest;
use App\Http\Requests\ReorderStudyCategoryRequest;
use App\Http\Requests\StoreStudyCategoryRequest;
use App\Http\Requests\UpdateStudyCategoryRequest;
use App\Http\Resources\StudyCategoryResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudyCategoryController extends BaseController
{
    public function __construct(private readonly StudyCategoryService $service)
    {
    }

    public function publicIndex(): JsonResponse
    {
        return $this->success(
            StudyCategoryResource::collection($this->service->listActive())->resolve(),
            'Study categories retrieved successfully.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudyCategory::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['is_active'],
            allowedSorts: ['display_order', 'name', 'created_at'],
            searchableColumns: ['name'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: StudyCategoryResource::collection($paginator)->resolve(),
            message: 'Study categories retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $studyCategory): JsonResponse
    {
        $model = $this->service->find($studyCategory);

        $this->authorize('view', $model);

        return $this->success(new StudyCategoryResource($model), 'Study category retrieved successfully.');
    }

    public function store(StoreStudyCategoryRequest $request): JsonResponse
    {
        $dto = TaxonomyDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new StudyCategoryResource($model), 'Study category created successfully.', status: 201);
    }

    public function update(UpdateStudyCategoryRequest $request, string $studyCategory): JsonResponse
    {
        $model = $this->service->find($studyCategory);

        $dto = PartialTaxonomyDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new StudyCategoryResource($updated), 'Study category updated successfully.');
    }

    public function destroy(string $studyCategory): JsonResponse
    {
        $model = $this->service->find($studyCategory);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Study category deleted successfully.');
    }

    public function restore(string $studyCategory): JsonResponse
    {
        $this->authorize('restore', StudyCategory::class);

        $restored = $this->service->restore($studyCategory);

        return $this->success(new StudyCategoryResource($restored), 'Study category restored successfully.');
    }

    public function bulkActivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', StudyCategory::class);

        $count = $this->service->bulkActivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Study categories activated successfully.');
    }

    public function bulkDeactivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', StudyCategory::class);

        $count = $this->service->bulkDeactivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Study categories deactivated successfully.');
    }

    public function bulkDelete(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('delete', StudyCategory::class);

        $count = $this->service->bulkDelete($request->validated('ids'));

        return $this->success(['deleted' => $count], 'Study categories deleted successfully.');
    }

    public function bulkRestore(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('restore', StudyCategory::class);

        $count = $this->service->bulkRestore($request->validated('ids'));

        return $this->success(['restored' => $count], 'Study categories restored successfully.');
    }

    public function reorder(ReorderStudyCategoryRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('order'));

        return $this->success(null, 'Study categories reordered successfully.');
    }
}
