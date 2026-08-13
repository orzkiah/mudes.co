<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\StudySchedule;
use App\Domain\Models\StudyScheduleOccurrence;
use Illuminate\Database\Eloquent\Collection;

interface StudyScheduleRepositoryInterface extends RepositoryInterface
{
    public function find(string $id): ?StudySchedule;

    public function findOrFail(string $id): StudySchedule;

    public function findTrashedOrFail(string $id): StudySchedule;

    /**
     * @return Collection<int, StudyScheduleOccurrence>
     */
    public function occurrencesFor(string $scheduleId): Collection;

    public function findOccurrenceOrFail(string $occurrenceId): StudyScheduleOccurrence;

    /**
     * Occurrence dates already generated for a schedule, for idempotent
     * generation.
     *
     * @return array<int, string>
     */
    public function existingOccurrenceDates(string $scheduleId): array;

    public function createOccurrence(string $scheduleId, string $date): StudyScheduleOccurrence;

    /**
     * @param array<string, mixed> $attributes
     */
    public function updateOccurrence(StudyScheduleOccurrence $occurrence, array $attributes): StudyScheduleOccurrence;
}
