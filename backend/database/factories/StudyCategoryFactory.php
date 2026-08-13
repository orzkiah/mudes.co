<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\StudyCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StudyCategory>
 */
class StudyCategoryFactory extends Factory
{
    protected $model = StudyCategory::class;

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
            'icon' => fake()->randomElement(['book', 'calendar', 'star']),
            'color' => fake()->hexColor(),
            'display_order' => 0,
            'is_active' => true,
        ];
    }
}
