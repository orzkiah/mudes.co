<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\PartialTaxonomyDTO;
use App\Application\DTO\TaxonomyDTO;
use App\Application\Services\GalleryCategoryService;
use App\Domain\Models\GalleryCategory;
use App\Http\Requests\BulkTaxonomyActionRequest;
use App\Http\Requests\ReorderGalleryCategoryRequest;
use App\Http\Requests\StoreGalleryCategoryRequest;
use App\Http\Requests\UpdateGalleryCategoryRequest;
use App\Http\Resources\GalleryCategoryResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryCategoryController extends BaseController
{
    public function __construct(private readonly GalleryCategoryService $service)
    {
    }

    public function publicIndex(): JsonResponse
    {
        return $this->success(
            GalleryCategoryResource::collection($this->service->listActive())->resolve(),
            'Gallery categories retrieved successfully.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GalleryCategory::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['is_active'],
            allowedSorts: ['display_order', 'name', 'created_at'],
            searchableColumns: ['name'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: GalleryCategoryResource::collection($paginator)->resolve(),
            message: 'Gallery categories retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $galleryCategory): JsonResponse
    {
        $model = $this->service->find($galleryCategory);

        $this->authorize('view', $model);

        return $this->success(new GalleryCategoryResource($model), 'Gallery category retrieved successfully.');
    }

    public function store(StoreGalleryCategoryRequest $request): JsonResponse
    {
        $dto = TaxonomyDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new GalleryCategoryResource($model), 'Gallery category created successfully.', status: 201);
    }

    public function update(UpdateGalleryCategoryRequest $request, string $galleryCategory): JsonResponse
    {
        $model = $this->service->find($galleryCategory);

        $dto = PartialTaxonomyDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new GalleryCategoryResource($updated), 'Gallery category updated successfully.');
    }

    public function destroy(string $galleryCategory): JsonResponse
    {
        $model = $this->service->find($galleryCategory);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Gallery category deleted successfully.');
    }

    public function restore(string $galleryCategory): JsonResponse
    {
        $this->authorize('restore', GalleryCategory::class);

        $restored = $this->service->restore($galleryCategory);

        return $this->success(new GalleryCategoryResource($restored), 'Gallery category restored successfully.');
    }

    public function bulkActivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', GalleryCategory::class);

        $count = $this->service->bulkActivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Gallery categories activated successfully.');
    }

    public function bulkDeactivate(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('update', GalleryCategory::class);

        $count = $this->service->bulkDeactivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Gallery categories deactivated successfully.');
    }

    public function bulkDelete(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('delete', GalleryCategory::class);

        $count = $this->service->bulkDelete($request->validated('ids'));

        return $this->success(['deleted' => $count], 'Gallery categories deleted successfully.');
    }

    public function bulkRestore(BulkTaxonomyActionRequest $request): JsonResponse
    {
        $this->authorize('restore', GalleryCategory::class);

        $count = $this->service->bulkRestore($request->validated('ids'));

        return $this->success(['restored' => $count], 'Gallery categories restored successfully.');
    }

    public function reorder(ReorderGalleryCategoryRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('order'));

        return $this->success(null, 'Gallery categories reordered successfully.');
    }
}
