<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\ActivityCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ActivityCategory>
 */
class ActivityCategoryFactory extends Factory
{
    protected $model = ActivityCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['calendar', 'users', 'flag']),
            'color' => fake()->hexColor(),
            'display_order' => 0,
            'is_active' => true,
        ];
    }
}
