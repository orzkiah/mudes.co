<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateStudyScheduleDTO;
use App\Application\DTO\UpdateStudyScheduleDTO;
use App\Application\Services\StudyScheduleService;
use App\Domain\Models\StudySchedule;
use App\Domain\Models\StudyScheduleOccurrence;

use App\Http\Requests\StoreStudyScheduleRequest;
use App\Http\Requests\UpdateStudyScheduleOccurrenceRequest;
use App\Http\Requests\UpdateStudyScheduleRequest;
use App\Http\Resources\StudyScheduleOccurrenceResource;
use App\Http\Resources\StudyScheduleResource;
use App\Shared\Support\CursorPaginationMeta;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudyScheduleController extends BaseController
{
    public function __construct(private readonly StudyScheduleService $service)
    {
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $filter = QueryFilter::fromRequest($request, allowedFilters: ['study_category_id']);
        $filter->apply($query = StudySchedule::query()->with('category')->where('is_active', true));

        $paginator = $query->cursorPaginate(min((int) $request->query('perPage', 20), 100));

        return $this->success(
            data: StudyScheduleResource::collection($paginator)->resolve(),
            message: 'Study schedules retrieved successfully.',
            meta: CursorPaginationMeta::fromPaginator($paginator),
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudySchedule::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['is_active', 'day_of_week', 'study_category_id'],
            allowedSorts: ['day_of_week', 'created_at'],
            searchableColumns: ['topic', 'ustadz_name'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: StudyScheduleResource::collection($paginator)->resolve(),
            message: 'Study schedules retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $schedule): JsonResponse
    {
        $model = $this->service->find($schedule);

        $this->authorize('view', $model);

        return $this->success(new StudyScheduleResource($model), 'Study schedule retrieved successfully.');
    }

    public function store(StoreStudyScheduleRequest $request): JsonResponse
    {
        $dto = CreateStudyScheduleDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new StudyScheduleResource($model), 'Study schedule created successfully.', status: 201);
    }

    public function update(UpdateStudyScheduleRequest $request, string $schedule): JsonResponse
    {
        $model = $this->service->find($schedule);

        $dto = UpdateStudyScheduleDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new StudyScheduleResource($updated), 'Study schedule updated successfully.');
    }

    public function destroy(string $schedule): JsonResponse
    {
        $model = $this->service->find($schedule);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->id], 'Study schedule deleted successfully.');
    }

    public function restore(string $schedule): JsonResponse
    {
        $this->authorize('restore', StudySchedule::class);

        $restored = $this->service->restore($schedule);

        return $this->success(new StudyScheduleResource($restored), 'Study schedule restored successfully.');
    }

    public function occurrences(string $schedule): JsonResponse
    {
        $model = $this->service->find($schedule);

        $this->authorize('view', $model);

        return $this->success(
            StudyScheduleOccurrenceResource::collection($this->service->occurrences($schedule))->resolve(),
            'Study schedule occurrences retrieved successfully.',
        );
    }

    public function generateOccurrences(Request $request, string $schedule): JsonResponse
    {
        $model = $this->service->find($schedule);

        $this->authorize('update', $model);

        $weeks = min((int) $request->query('weeks', 8), 52);
        $created = $this->service->generateOccurrences($model, $weeks);

        return $this->success(['created' => $created], 'Study schedule occurrences generated successfully.');
    }

    public function updateOccurrence(UpdateStudyScheduleOccurrenceRequest $request, string $occurrence): JsonResponse
    {
        /** @var array{status?: string, overrideNote?: ?string} $validated */
        $validated = $request->validated();

        $updated = $this->service->updateOccurrence(
            $occurrence,
            $validated['status'] ?? null,
            $validated['overrideNote'] ?? null,
        );

        return $this->success(new StudyScheduleOccurrenceResource($updated), 'Study schedule occurrence updated successfully.');
    }

    public function allOccurrences(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudySchedule::class);

        $query = StudyScheduleOccurrence::query()
            ->with(['schedule.category'])
            ->orderBy('occurrence_date', 'desc');

        $paginator = $query->cursorPaginate(min((int) $request->query('perPage', 50), 100));

        return $this->success(
            data: StudyScheduleOccurrenceResource::collection($paginator)->resolve(),
            message: 'Study schedule occurrences retrieved successfully.',
            meta: CursorPaginationMeta::fromPaginator($paginator),
        );
    }
}

