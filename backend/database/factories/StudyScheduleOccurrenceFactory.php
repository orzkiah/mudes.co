<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\StudySchedule;
use App\Domain\Models\StudyScheduleOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyScheduleOccurrence>
 */
class StudyScheduleOccurrenceFactory extends Factory
{
    protected $model = StudyScheduleOccurrence::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_schedule_id' => StudySchedule::factory(),
            'occurrence_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'status' => 'scheduled',
        ];
    }
}
