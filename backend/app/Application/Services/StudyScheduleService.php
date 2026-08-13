<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\StudySchedule;
use App\Domain\Models\StudyScheduleOccurrence;
use App\Infrastructure\Repositories\Contracts\StudyScheduleRepositoryInterface;
use App\Shared\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class StudyScheduleService extends BaseService
{
    public function __construct(private readonly StudyScheduleRepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage, ?QueryFilter $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filter);
    }

    public function find(string $id): StudySchedule
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): StudySchedule
    {
        return $this->transaction(fn () => $this->repository->create($attributes));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(StudySchedule $schedule, array $attributes): StudySchedule
    {
        return $this->transaction(fn () => $this->repository->update($schedule, $attributes));
    }

    public function delete(StudySchedule $schedule): bool
    {
        return $this->transaction(fn () => $this->repository->delete($schedule));
    }

    public function restore(string $id): StudySchedule
    {
        return $this->transaction(function () use ($id) {
            $schedule = $this->repository->findTrashedOrFail($id);
            $this->repository->restore($schedule);

            return $schedule->refresh();
        });
    }

    /**
     * @return Collection<int, StudyScheduleOccurrence>
     */
    public function occurrences(string $scheduleId): Collection
    {
        return $this->repository->occurrencesFor($scheduleId);
    }

    /**
     * Generates dated occurrences matching the schedule's day_of_week for
     * the next $weeks weeks, skipping dates already generated
     * (PROJECT_SPECIFICATION.md §3.7 - idempotent, template stays
     * unaltered).
     */
    public function generateOccurrences(StudySchedule $schedule, int $weeks = 8): int
    {
        return $this->transaction(function () use ($schedule, $weeks) {
            $existing = $this->repository->existingOccurrenceDates((string) $schedule->id);
            $created = 0;
            $cursor = Carbon::today();
            $dayOfWeek = (int) $schedule->day_of_week;

            for ($i = 0; $i < $weeks * 7; $i++) {
                $candidate = $cursor->copy()->addDays($i);

                if ((int) $candidate->dayOfWeek !== $dayOfWeek) {
                    continue;
                }

                $date = $candidate->toDateString();

                if (in_array($date, $existing, true)) {
                    continue;
                }

                $this->repository->createOccurrence((string) $schedule->id, $date);
                $created++;
            }

            return $created;
        });
    }

    public function updateOccurrence(string $occurrenceId, ?string $status, ?string $overrideNote): StudyScheduleOccurrence
    {
        return $this->transaction(function () use ($occurrenceId, $status, $overrideNote) {
            $occurrence = $this->repository->findOccurrenceOrFail($occurrenceId);

            $attributes = array_filter([
                'status' => $status,
                'override_note' => $overrideNote,
            ], fn ($value) => $value !== null);

            return $this->repository->updateOccurrence($occurrence, $attributes);
        });
    }
}
