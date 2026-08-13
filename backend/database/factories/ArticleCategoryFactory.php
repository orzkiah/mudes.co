<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ArticleCategory>
 */
class ArticleCategoryFactory extends Factory
{
    protected $model = ArticleCategory::class;

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
            'icon' => fake()->randomElement(['newspaper', 'pen', 'megaphone']),
            'color' => fake()->hexColor(),
            'display_order' => 0,
            'is_active' => true,
        ];
    }
}
