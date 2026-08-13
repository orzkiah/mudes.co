<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\Gallery;
use App\Domain\Models\GalleryCategory;
use App\Domain\Models\Media;
use Illuminate\Http\Request;

/**
 * @property Gallery $resource
 */
class GalleryResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Gallery $gallery */
        $gallery = $this->resource;

        /** @var GalleryCategory|null $category */
        $category = $gallery->category;

        /** @var Media|null $coverPhoto */
        $coverPhoto = $gallery->coverPhoto;

        return [
            'id' => $gallery->id,
            'galleryCategoryId' => $gallery->gallery_category_id,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'color' => $category->color,
            ] : null,
            'activityId' => $gallery->activity_id,
            'title' => $gallery->title,
            'description' => $gallery->description,
            'coverPhoto' => $coverPhoto ? [
                'id' => $coverPhoto->id,
                'url' => $coverPhoto->getUrl(),
            ] : null,
            'photoCount' => $gallery->photos()->count(),
            'photos' => GalleryPhotoResource::collection($gallery->photos)->resolve(),
            'createdAt' => $gallery->created_at?->toIso8601String(),
            'updatedAt' => $gallery->updated_at?->toIso8601String(),
        ];
    }
}
