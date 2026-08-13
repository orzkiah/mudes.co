<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['megaphone', 'camera', 'book', 'users']),
            'color' => fake()->hexColor(),
            'display_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
