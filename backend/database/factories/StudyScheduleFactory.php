<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\StudyCategory;
use App\Domain\Models\StudySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudySchedule>
 */
class StudyScheduleFactory extends Factory
{
    protected $model = StudySchedule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_category_id' => StudyCategory::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'topic' => fake()->sentence(3),
            'ustadz_name' => fake()->name(),
            'location' => fake()->streetAddress(),
            'is_active' => true,
        ];
    }
}
