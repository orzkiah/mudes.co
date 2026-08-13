<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\Gallery;
use App\Domain\Models\GalleryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gallery_category_id' => GalleryCategory::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
        ];
    }
}
