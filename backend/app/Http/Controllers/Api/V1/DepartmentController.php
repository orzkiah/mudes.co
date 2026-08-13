<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateDepartmentDTO;
use App\Application\DTO\UpdateDepartmentDTO;
use App\Application\Services\DepartmentService;
use App\Domain\Models\Department;
use App\Http\Requests\BulkDepartmentActionRequest;
use App\Http\Requests\ReorderDepartmentRequest;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends BaseController
{
    public function __construct(private readonly DepartmentService $service)
    {
    }

    /**
     * GET /public/departments - active only, sorted by displayOrder.
     */
    public function publicIndex(): JsonResponse
    {
        $departments = $this->service->listActive();

        return $this->success(
            DepartmentResource::collection($departments)->resolve(),
            'Departments retrieved successfully.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Department::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['is_active'],
            allowedSorts: ['display_order', 'name', 'created_at'],
            searchableColumns: ['name'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: DepartmentResource::collection($paginator)->resolve(),
            message: 'Departments retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $department): JsonResponse
    {
        $model = $this->service->find($department);

        $this->authorize('view', $model);

        return $this->success(new DepartmentResource($model), 'Department retrieved successfully.');
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $dto = CreateDepartmentDTO::fromRequest($request);

        $department = $this->service->create($dto);

        return $this->success(new DepartmentResource($department), 'Department created successfully.', status: 201);
    }

    public function update(UpdateDepartmentRequest $request, string $department): JsonResponse
    {
        $model = $this->service->find($department);

        $dto = UpdateDepartmentDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto);

        return $this->success(new DepartmentResource($updated), 'Department updated successfully.');
    }

    public function destroy(string $department): JsonResponse
    {
        $model = $this->service->find($department);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->id], 'Department deleted successfully.');
    }

    public function restore(string $department): JsonResponse
    {
        $this->authorize('restore', Department::class);

        $restored = $this->service->restore($department);

        return $this->success(new DepartmentResource($restored), 'Department restored successfully.');
    }

    public function bulkActivate(BulkDepartmentActionRequest $request): JsonResponse
    {
        $this->authorize('update', Department::class);

        $count = $this->service->bulkActivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Departments activated successfully.');
    }

    public function bulkDeactivate(BulkDepartmentActionRequest $request): JsonResponse
    {
        $this->authorize('update', Department::class);

        $count = $this->service->bulkDeactivate($request->validated('ids'));

        return $this->success(['updated' => $count], 'Departments deactivated successfully.');
    }

    public function bulkDelete(BulkDepartmentActionRequest $request): JsonResponse
    {
        $this->authorize('delete', Department::class);

        $count = $this->service->bulkDelete($request->validated('ids'));

        return $this->success(['deleted' => $count], 'Departments deleted successfully.');
    }

    public function bulkRestore(BulkDepartmentActionRequest $request): JsonResponse
    {
        $this->authorize('restore', Department::class);

        $count = $this->service->bulkRestore($request->validated('ids'));

        return $this->success(['restored' => $count], 'Departments restored successfully.');
    }

    public function reorder(ReorderDepartmentRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('order'));

        return $this->success(null, 'Departments reordered successfully.');
    }
}
