<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateAttendanceSessionDTO;
use App\Application\DTO\UpdateAttendanceSessionDTO;
use App\Application\Services\AttendanceSessionService;
use App\Domain\Models\AttendanceSession;
use App\Http\Requests\ManualCheckInRequest;
use App\Http\Requests\QrCheckInRequest;
use App\Http\Requests\StoreAttendanceSessionRequest;
use App\Http\Requests\UpdateAttendanceSessionRequest;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\AttendanceSessionResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceSessionController extends BaseController
{
    public function __construct(private readonly AttendanceSessionService $service)
    {
    }

    /**
     * Public, token-scoped, no login required (PROJECT_SPECIFICATION.md
     * §15) - rate-limited via the 'throttle' middleware on this route.
     */
    public function checkIn(QrCheckInRequest $request): JsonResponse
    {
        /** @var array{qrToken: string, memberId: string} $validated */
        $validated = $request->validated();

        $attendance = $this->service->checkInByToken($validated['qrToken'], $validated['memberId']);

        return $this->success(new AttendanceResource($attendance), 'Checked in successfully.', status: 201);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['source_type', 'source_id'],
            allowedSorts: ['opens_at', 'created_at'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: AttendanceSessionResource::collection($paginator)->resolve(),
            message: 'Attendance sessions retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $session): JsonResponse
    {
        $model = $this->service->find($session);

        $this->authorize('view', $model);

        return $this->success(new AttendanceSessionResource($model), 'Attendance session retrieved successfully.');
    }

    public function store(StoreAttendanceSessionRequest $request): JsonResponse
    {
        $dto = CreateAttendanceSessionDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new AttendanceSessionResource($model), 'Attendance session created successfully.', status: 201);
    }

    public function update(UpdateAttendanceSessionRequest $request, string $session): JsonResponse
    {
        $model = $this->service->find($session);

        $dto = UpdateAttendanceSessionDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new AttendanceSessionResource($updated), 'Attendance session updated successfully.');
    }

    public function destroy(string $session): JsonResponse
    {
        $model = $this->service->find($session);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Attendance session deleted successfully.');
    }

    public function restore(string $session): JsonResponse
    {
        $this->authorize('restore', AttendanceSession::class);

        $restored = $this->service->restore($session);

        return $this->success(new AttendanceSessionResource($restored), 'Attendance session restored successfully.');
    }

    public function roster(string $session): JsonResponse
    {
        $model = $this->service->find($session);

        $this->authorize('view', $model);

        return $this->success(
            AttendanceResource::collection($this->service->roster($session))->resolve(),
            'Attendance roster retrieved successfully.',
        );
    }

    public function manualCheckIn(ManualCheckInRequest $request, string $session): JsonResponse
    {
        $model = $this->service->find($session);

        /** @var array{memberId?: ?string, memberName?: ?string} $validated */
        $validated = $request->validated();

        $attendance = $this->service->manualCheckIn($model, $validated['memberId'] ?? null, $validated['memberName'] ?? null);

        return $this->success(new AttendanceResource($attendance), 'Member checked in successfully.', status: 201);
    }
}
