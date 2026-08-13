<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateGalleryDTO;
use App\Application\DTO\UpdateGalleryDTO;
use App\Application\Services\GalleryService;
use App\Domain\Models\Gallery;
use App\Http\Requests\AttachGalleryPhotosRequest;
use App\Http\Requests\ReorderGalleryPhotosRequest;
use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Http\Resources\GalleryResource;
use App\Shared\Support\CursorPaginationMeta;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends BaseController
{
    public function __construct(private readonly GalleryService $service)
    {
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['gallery_category_id', 'activity_id'],
            searchableColumns: ['title'],
        );

        $query = Gallery::query()->with(['category', 'coverPhoto', 'photos.media']);
        $filter->apply($query);

        $paginator = $query->cursorPaginate(min((int) $request->query('perPage', 20), 100));

        return $this->success(
            data: GalleryResource::collection($paginator)->resolve(),
            message: 'Galleries retrieved successfully.',
            meta: CursorPaginationMeta::fromPaginator($paginator),
        );
    }

    public function publicShow(string $gallery): JsonResponse
    {
        $model = $this->service->find($gallery);

        return $this->success(new GalleryResource($model), 'Gallery retrieved successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Gallery::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['gallery_category_id', 'activity_id'],
            allowedSorts: ['created_at', 'title'],
            searchableColumns: ['title'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: GalleryResource::collection($paginator)->resolve(),
            message: 'Galleries retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $gallery): JsonResponse
    {
        $model = $this->service->find($gallery);

        $this->authorize('view', $model);

        return $this->success(new GalleryResource($model), 'Gallery retrieved successfully.');
    }

    public function store(StoreGalleryRequest $request): JsonResponse
    {
        $dto = CreateGalleryDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new GalleryResource($model), 'Gallery created successfully.', status: 201);
    }

    public function update(UpdateGalleryRequest $request, string $gallery): JsonResponse
    {
        $model = $this->service->find($gallery);

        $dto = UpdateGalleryDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new GalleryResource($updated), 'Gallery updated successfully.');
    }

    public function destroy(string $gallery): JsonResponse
    {
        $model = $this->service->find($gallery);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Gallery deleted successfully.');
    }

    public function restore(string $gallery): JsonResponse
    {
        $this->authorize('restore', Gallery::class);

        $restored = $this->service->restore($gallery);

        return $this->success(new GalleryResource($restored), 'Gallery restored successfully.');
    }

    public function attachPhotos(AttachGalleryPhotosRequest $request, string $gallery): JsonResponse
    {
        $model = $this->service->find($gallery);

        $this->authorize('update', $model);

        $this->service->addPhotos($gallery, $request->validated('mediaIds'));

        return $this->success(new GalleryResource($model->fresh(['photos.media'])), 'Photos attached successfully.', status: 201);
    }

    public function removePhoto(string $gallery, string $photo): JsonResponse
    {
        $model = $this->service->find($gallery);

        $this->authorize('update', $model);

        $this->service->removePhoto($photo);

        return $this->success(['id' => $photo], 'Photo removed successfully.');
    }

    public function reorderPhotos(ReorderGalleryPhotosRequest $request, string $gallery): JsonResponse
    {
        $model = $this->service->find($gallery);

        $this->authorize('update', $model);

        $this->service->reorderPhotos($request->validated('order'));

        return $this->success(new GalleryResource($model->fresh(['photos.media'])), 'Photos reordered successfully.');
    }
}
