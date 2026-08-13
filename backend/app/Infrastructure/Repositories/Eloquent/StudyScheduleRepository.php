<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\StudySchedule;
use App\Domain\Models\StudyScheduleOccurrence;
use App\Infrastructure\Repositories\Contracts\StudyScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class StudyScheduleRepository extends BaseRepository implements StudyScheduleRepositoryInterface
{
    public function __construct(StudySchedule $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?StudySchedule
    {
        return StudySchedule::query()->with('category')->find($id);
    }

    public function findOrFail(string $id): StudySchedule
    {
        return StudySchedule::query()->with('category')->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): StudySchedule
    {
        return StudySchedule::withTrashed()->with('category')->findOrFail($id);
    }

    public function occurrencesFor(string $scheduleId): Collection
    {
        return StudyScheduleOccurrence::query()
            ->where('study_schedule_id', $scheduleId)
            ->orderBy('occurrence_date')
            ->get();
    }

    public function findOccurrenceOrFail(string $occurrenceId): StudyScheduleOccurrence
    {
        return StudyScheduleOccurrence::query()->findOrFail($occurrenceId);
    }

    public function existingOccurrenceDates(string $scheduleId): array
    {
        return StudyScheduleOccurrence::query()
            ->where('study_schedule_id', $scheduleId)
            ->pluck('occurrence_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->all();
    }

    public function createOccurrence(string $scheduleId, string $date): StudyScheduleOccurrence
    {
        return StudyScheduleOccurrence::query()->create([
            'study_schedule_id' => $scheduleId,
            'occurrence_date' => $date,
            'status' => 'scheduled',
        ]);
    }

    public function updateOccurrence(StudyScheduleOccurrence $occurrence, array $attributes): StudyScheduleOccurrence
    {
        $occurrence->update($attributes);

        return $occurrence->refresh();
    }
}
