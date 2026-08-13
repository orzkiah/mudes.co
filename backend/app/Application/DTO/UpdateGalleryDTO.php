<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateGalleryDTO extends BaseDTO
{
    public function __construct(
        public readonly string $galleryCategoryId,
        public readonly ?string $activityId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $coverPhotoMediaId,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{galleryCategoryId: string, activityId?: ?string, title: string, description?: ?string, coverPhotoMediaId?: ?string} $validated */
        $validated = $request->validated();

        return new self(
            galleryCategoryId: $validated['galleryCategoryId'],
            activityId: $validated['activityId'] ?? null,
            title: $validated['title'],
            description: $validated['description'] ?? null,
            coverPhotoMediaId: $validated['coverPhotoMediaId'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'gallery_category_id' => $this->galleryCategoryId,
            'activity_id' => $this->activityId,
            'title' => $this->title,
            'description' => $this->description,
            'cover_photo_media_id' => $this->coverPhotoMediaId,
        ];
    }
}
