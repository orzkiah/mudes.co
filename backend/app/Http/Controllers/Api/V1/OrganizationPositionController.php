<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateOrganizationPositionDTO;
use App\Application\DTO\UpdateOrganizationPositionDTO;
use App\Application\Services\OrganizationPositionService;
use App\Domain\Models\OrganizationPosition;
use App\Http\Requests\ReorderOrganizationPositionRequest;
use App\Http\Requests\StoreOrganizationPositionRequest;
use App\Http\Requests\UpdateOrganizationPositionRequest;
use App\Http\Resources\OrganizationPositionResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationPositionController extends BaseController
{
    public function __construct(private readonly OrganizationPositionService $service)
    {
    }

    public function publicStructure(): JsonResponse
    {
        $tree = $this->service->activePeriodTree();

        return $this->success(
            OrganizationPositionResource::collection($tree)->resolve(),
            'Organization structure retrieved successfully.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganizationPosition::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['department_id', 'position_type'],
            allowedSorts: ['display_order', 'title', 'level', 'created_at'],
            searchableColumns: ['title'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: OrganizationPositionResource::collection($paginator)->resolve(),
            message: 'Organization positions retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function tree(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganizationPosition::class);

        $validated = $request->validate(['organizationPeriodId' => ['required', 'uuid']]);

        $tree = $this->service->tree($validated['organizationPeriodId']);

        return $this->success(
            OrganizationPositionResource::collection($tree)->resolve(),
            'Organization position tree retrieved successfully.',
        );
    }

    public function show(string $position): JsonResponse
    {
        $model = $this->service->find($position);

        $this->authorize('view', $model);

        return $this->success(new OrganizationPositionResource($model), 'Organization position retrieved successfully.');
    }

    public function store(StoreOrganizationPositionRequest $request): JsonResponse
    {
        $dto = CreateOrganizationPositionDTO::fromRequest($request);

        $position = $this->service->create($dto);

        return $this->success(new OrganizationPositionResource($position), 'Organization position created successfully.', status: 201);
    }

    public function update(UpdateOrganizationPositionRequest $request, string $position): JsonResponse
    {
        $model = $this->service->find($position);

        $dto = UpdateOrganizationPositionDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto);

        return $this->success(new OrganizationPositionResource($updated), 'Organization position updated successfully.');
    }

    public function destroy(string $position): JsonResponse
    {
        $model = $this->service->find($position);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->id], 'Organization position deleted successfully.');
    }

    public function restore(string $position): JsonResponse
    {
        $this->authorize('restore', OrganizationPosition::class);

        $restored = $this->service->restore($position);

        return $this->success(new OrganizationPositionResource($restored), 'Organization position restored successfully.');
    }

    public function reorder(ReorderOrganizationPositionRequest $request, string $position): JsonResponse
    {
        $model = $this->service->find($position);

        /** @var array{displayOrder: int, parentPositionId?: ?string, departmentId?: ?string} $validated */
        $validated = $request->validated();

        $result = $this->service->reorder(
            $model,
            $validated['displayOrder'],
            $validated['parentPositionId'] ?? null,
            $validated['departmentId'] ?? null,
        );

        $resource = (new OrganizationPositionResource($result['position']))->resolve();
        $resource['affectedDescendantCount'] = $result['affectedDescendantCount'];

        return $this->success($resource, 'Position reordered successfully.');
    }
}
