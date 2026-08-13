<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\PartialTaxonomyDTO;
use App\Application\DTO\TaxonomyDTO;
use App\Application\Services\LibraryCategoryService;
use App\Domain\Models\LibraryCategory;
use App\Http\Requests\BulkTaxonomyActionRequest;
use App\Http\Requests\ReorderLibraryCategoryRequest;
use App\Http\Requests\StoreLibraryCategoryRequest;
use App\Http\Requests\UpdateLibraryCategoryRequest;
use App\Http\Resources\LibraryCategoryResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryCategoryController extends BaseController
{
    public function __construct(private readonly LibraryCategoryService $service)
    {
    }

    public function publicIndex(): JsonResponse
    {
        return $this->success(
            LibraryCategoryResource::collection($this->service->listActive())->resolve(),
            'Library categories retrieved successfully.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LibraryCategory::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['is_active'],
            allowedSorts: ['display_order', 'name', 'created_at'],
            searchableColumns: ['name'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: LibraryCategoryResource::collection($paginator)->resolve(),
            message: 'Library categories retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $libraryCategory): JsonResponse
    {
        $model = $this->service->find($libraryCategory);

        $this->authorize('view', $model);

        return $this->success(new LibraryCategoryResource($model), 'Library category retrieved successfully.');
    }

    public function store(StoreLibraryCategoryRequest $request): JsonResponse
    {
        $dto = TaxonomyDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new LibraryCategoryResource($model), 'Library category created successfully.', status: 201);
    }

    public function update(UpdateLibraryCategoryRequest $request, string $libraryCategory): JsonResponse
    {
        $model = $this->service->find($libraryCategory);

        $dto = PartialTaxonomyDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new LibraryCategoryResource($updated), 'Library category updated successfully.');
    }

    public function destroy(string $libraryCategory): JsonResponse
    {
        $model = $this->service->find($libraryCategory);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Library category deleted successfully.');
    }

    public function restore(string $libraryCategory): JsonResponse
    {
        $this->authorize('restore', LibraryCategory::class);

        $restored = $this->service->restore($libraryCategory);

        return $this->success(new LibraryCategoryResource($restored), 'Library category restored successfully.');
    }

    public function bulkActivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', LibraryCategory::class);

        $count = $this->service->bulkActivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Library categories activated successfully.');
    }

    public function bulkDeactivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', LibraryCategory::class);

        $count = $this->service->bulkDeactivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Library categories deactivated successfully.');
    }

    public function bulkDelete(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('delete', LibraryCategory::class);

        $count = $this->service->bulkDelete($request->validated('ids'));

        return $this->success(['deleted' => $count], 'Library categories deleted successfully.');
    }

    public function bulkRestore(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('restore', LibraryCategory::class);

        $count = $this->service->bulkRestore($request->validated('ids'));

        return $this->success(['restored' => $count], 'Library categories restored successfully.');
    }

    public function reorder(ReorderLibraryCategoryRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('order'));

        return $this->success(null, 'Library categories reordered successfully.');
    }
}
