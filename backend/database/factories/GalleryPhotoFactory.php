<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\Gallery;
use App\Domain\Models\GalleryPhoto;
use App\Domain\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GalleryPhoto>
 */
class GalleryPhotoFactory extends Factory
{
    protected $model = GalleryPhoto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gallery = Gallery::factory()->create();

        $media = Media::query()->forceCreate([
            'id' => (string) Str::uuid7(),
            'model_type' => Gallery::class,
            'model_id' => $gallery->id,
            'collection_name' => 'gallery-photo',
            'name' => 'photo',
            'file_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => 1024,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ]);

        return [
            'gallery_id' => $gallery->id,
            'media_id' => $media->id,
            'caption' => fake()->sentence(),
            'display_order' => 0,
        ];
    }
}
