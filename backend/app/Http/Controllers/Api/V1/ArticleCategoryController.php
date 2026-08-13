<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\PartialTaxonomyDTO;
use App\Application\DTO\TaxonomyDTO;
use App\Application\Services\ArticleCategoryService;
use App\Domain\Models\ArticleCategory;
use App\Http\Requests\BulkTaxonomyActionRequest;
use App\Http\Requests\ReorderArticleCategoryRequest;
use App\Http\Requests\StoreArticleCategoryRequest;
use App\Http\Requests\UpdateArticleCategoryRequest;
use App\Http\Resources\ArticleCategoryResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleCategoryController extends BaseController
{
    public function __construct(private readonly ArticleCategoryService $service)
    {
    }

    public function publicIndex(): JsonResponse
    {
        return $this->success(
            ArticleCategoryResource::collection($this->service->listActive())->resolve(),
            'Article categories retrieved successfully.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ArticleCategory::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['is_active'],
            allowedSorts: ['display_order', 'name', 'created_at'],
            searchableColumns: ['name'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: ArticleCategoryResource::collection($paginator)->resolve(),
            message: 'Article categories retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $articleCategory): JsonResponse
    {
        $model = $this->service->find($articleCategory);

        $this->authorize('view', $model);

        return $this->success(new ArticleCategoryResource($model), 'Article category retrieved successfully.');
    }

    public function store(StoreArticleCategoryRequest $request): JsonResponse
    {
        $dto = TaxonomyDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new ArticleCategoryResource($model), 'Article category created successfully.', status: 201);
    }

    public function update(UpdateArticleCategoryRequest $request, string $articleCategory): JsonResponse
    {
        $model = $this->service->find($articleCategory);

        $dto = PartialTaxonomyDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new ArticleCategoryResource($updated), 'Article category updated successfully.');
    }

    public function destroy(string $articleCategory): JsonResponse
    {
        $model = $this->service->find($articleCategory);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Article category deleted successfully.');
    }

    public function restore(string $articleCategory): JsonResponse
    {
        $this->authorize('restore', ArticleCategory::class);

        $restored = $this->service->restore($articleCategory);

        return $this->success(new ArticleCategoryResource($restored), 'Article category restored successfully.');
    }

    public function bulkActivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', ArticleCategory::class);

        $count = $this->service->bulkActivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Article categories activated successfully.');
    }

    public function bulkDeactivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', ArticleCategory::class);

        $count = $this->service->bulkDeactivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Article categories deactivated successfully.');
    }

    public function bulkDelete(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('delete', ArticleCategory::class);

        $count = $this->service->bulkDelete($request->validated('ids'));

        return $this->success(['deleted' => $count], 'Article categories deleted successfully.');
    }

    public function bulkRestore(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('restore', ArticleCategory::class);

        $count = $this->service->bulkRestore($request->validated('ids'));

        return $this->success(['restored' => $count], 'Article categories restored successfully.');
    }

    public function reorder(ReorderArticleCategoryRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('order'));

        return $this->success(null, 'Article categories reordered successfully.');
    }
}
