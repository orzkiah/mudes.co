<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\LibraryCategory;
use App\Domain\Models\LibraryDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryDocument>
 */
class LibraryDocumentFactory extends Factory
{
    protected $model = LibraryDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'library_category_id' => LibraryCategory::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'external_url' => 'https://example.com/'.fake()->slug(),
            'visibility' => 'public',
            'download_count' => 0,
        ];
    }
}
