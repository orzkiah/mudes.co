<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\Activity;
use App\Domain\Models\ActivityCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'activity_category_id' => ActivityCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'start_at' => fake()->dateTimeBetween('now', '+2 months'),
            'location' => fake()->address(),
            'status' => 'upcoming',
        ];
    }
}
