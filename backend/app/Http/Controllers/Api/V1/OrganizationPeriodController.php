<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\OrganizationPeriodService;
use App\Domain\Models\OrganizationPeriod;
use App\Http\Requests\StoreOrganizationPeriodRequest;
use App\Http\Requests\UpdateOrganizationPeriodRequest;
use App\Http\Resources\OrganizationPeriodResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Implements the Organization Periods endpoints specified in
 * API_SPECIFICATION.md §9.8:
 *   GET/POST  /admin/organization/periods
 *   GET/PUT/DELETE  /admin/organization/periods/{id}
 *   POST  /admin/organization/periods/{id}/activate
 */
class OrganizationPeriodController extends BaseController
{
    public function __construct(private readonly OrganizationPeriodService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganizationPeriod::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['is_active'],
            allowedSorts: ['start_date', 'end_date', 'label', 'created_at'],
            searchableColumns: ['label'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: OrganizationPeriodResource::collection($paginator)->resolve(),
            message: 'Organization periods retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $period): JsonResponse
    {
        $model = $this->service->find($period);

        $this->authorize('view', $model);

        return $this->success(new OrganizationPeriodResource($model), 'Organization period retrieved successfully.');
    }

    public function store(StoreOrganizationPeriodRequest $request): JsonResponse
    {
        /** @var array{label: string, startDate: string, endDate: string, isActive?: bool} $validated */
        $validated = $request->validated();

        $attributes = [
            'label' => $validated['label'],
            'start_date' => $validated['startDate'],
            'end_date' => $validated['endDate'],
            'is_active' => $validated['isActive'] ?? false,
        ];

        $period = $this->service->create($attributes);

        return $this->success(new OrganizationPeriodResource($period), 'Organization period created successfully.', status: 201);
    }

    public function update(UpdateOrganizationPeriodRequest $request, string $period): JsonResponse
    {
        $model = $this->service->find($period);

        /** @var array{label: string, startDate: string, endDate: string} $validated */
        $validated = $request->validated();

        $attributes = [
            'label' => $validated['label'],
            'start_date' => $validated['startDate'],
            'end_date' => $validated['endDate'],
        ];

        $updated = $this->service->update($model, $attributes);

        return $this->success(new OrganizationPeriodResource($updated), 'Organization period updated successfully.');
    }

    public function destroy(string $period): JsonResponse
    {
        $model = $this->service->find($period);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->id], 'Organization period deleted successfully.');
    }

    public function restore(string $period): JsonResponse
    {
        $this->authorize('restore', OrganizationPeriod::class);

        $restored = $this->service->restore($period);

        return $this->success(new OrganizationPeriodResource($restored), 'Organization period restored successfully.');
    }

    /**
     * POST /admin/organization/periods/{id}/activate
     * Deactivates any currently active period and activates this one.
     */
    public function activate(string $period): JsonResponse
    {
        $model = $this->service->find($period);

        $this->authorize('activate', $model);

        $activated = $this->service->activate($model);

        return $this->success(new OrganizationPeriodResource($activated), 'Organization period activated successfully.');
    }
}
