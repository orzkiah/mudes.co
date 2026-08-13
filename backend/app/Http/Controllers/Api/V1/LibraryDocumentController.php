<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateLibraryDocumentDTO;
use App\Application\DTO\UpdateLibraryDocumentDTO;
use App\Application\Services\LibraryDocumentService;
use App\Domain\Enums\LibraryDocumentVisibility;
use App\Domain\Models\LibraryDocument;
use App\Http\Requests\StoreLibraryDocumentRequest;
use App\Http\Requests\UpdateLibraryDocumentRequest;
use App\Http\Resources\LibraryDocumentResource;
use App\Shared\Support\CursorPaginationMeta;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryDocumentController extends BaseController
{
    public function __construct(private readonly LibraryDocumentService $service)
    {
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['library_category_id'],
            searchableColumns: ['title', 'description'],
        );

        $query = LibraryDocument::query()->with(['category', 'file'])->where('visibility', 'public');
        $filter->apply($query);

        $paginator = $query->cursorPaginate(min((int) $request->query('perPage', 20), 100));

        return $this->success(
            data: LibraryDocumentResource::collection($paginator)->resolve(),
            message: 'Library documents retrieved successfully.',
            meta: CursorPaginationMeta::fromPaginator($paginator),
        );
    }

    public function publicShow(string $document): JsonResponse
    {
        $model = $this->service->find($document);

        /** @var LibraryDocumentVisibility $visibility */
        $visibility = $model->visibility;

        abort_if($visibility->value !== 'public', 404);

        $this->service->incrementDownloadCount($model);

        return $this->success(new LibraryDocumentResource($model), 'Library document retrieved successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LibraryDocument::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['library_category_id', 'visibility'],
            allowedSorts: ['created_at', 'title'],
            searchableColumns: ['title', 'description'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: LibraryDocumentResource::collection($paginator)->resolve(),
            message: 'Library documents retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $document): JsonResponse
    {
        $model = $this->service->find($document);

        $this->authorize('view', $model);

        return $this->success(new LibraryDocumentResource($model), 'Library document retrieved successfully.');
    }

    public function store(StoreLibraryDocumentRequest $request): JsonResponse
    {
        $dto = CreateLibraryDocumentDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new LibraryDocumentResource($model), 'Library document created successfully.', status: 201);
    }

    public function update(UpdateLibraryDocumentRequest $request, string $document): JsonResponse
    {
        $model = $this->service->find($document);

        $dto = UpdateLibraryDocumentDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new LibraryDocumentResource($updated), 'Library document updated successfully.');
    }

    public function destroy(string $document): JsonResponse
    {
        $model = $this->service->find($document);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Library document deleted successfully.');
    }

    public function restore(string $document): JsonResponse
    {
        $this->authorize('restore', LibraryDocument::class);

        $restored = $this->service->restore($document);

        return $this->success(new LibraryDocumentResource($restored), 'Library document restored successfully.');
    }
}
