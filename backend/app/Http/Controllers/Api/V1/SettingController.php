<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateSettingDTO;
use App\Application\DTO\UpdateSettingDTO;
use App\Application\Services\SettingService;
use App\Domain\Models\Setting;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function __construct(private readonly SettingService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['group', 'type', 'is_encrypted'],
            allowedSorts: ['key', 'group', 'created_at', 'updated_at'],
            searchableColumns: ['key', 'description'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: SettingResource::collection($paginator)->resolve(),
            message: 'Settings retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $setting): JsonResponse
    {
        $model = $this->service->find($setting);

        $this->authorize('view', $model);

        return $this->success(new SettingResource($model), 'Setting retrieved successfully.');
    }

    public function store(StoreSettingRequest $request): JsonResponse
    {
        $dto = CreateSettingDTO::fromRequest($request);

        $setting = $this->service->create($dto);

        return $this->success(new SettingResource($setting), 'Setting created successfully.', status: 201);
    }

    public function update(UpdateSettingRequest $request, string $setting): JsonResponse
    {
        $model = $this->service->find($setting);

        $dto = UpdateSettingDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto);

        return $this->success(new SettingResource($updated), 'Setting updated successfully.');
    }

    public function destroy(string $setting): JsonResponse
    {
        $model = $this->service->find($setting);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->id], 'Setting deleted successfully.');
    }

    public function restore(string $setting): JsonResponse
    {
        $this->authorize('restore', Setting::class);

        $restored = $this->service->restore($setting);

        return $this->success(new SettingResource($restored), 'Setting restored successfully.');
    }
}
