<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\GalleryPhoto;
use App\Domain\Models\Media;
use Illuminate\Http\Request;

/**
 * @property GalleryPhoto $resource
 */
class GalleryPhotoResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var GalleryPhoto $photo */
        $photo = $this->resource;

        /** @var Media|null $media */
        $media = $photo->media;

        return [
            'id' => $photo->id,
            'mediaId' => $photo->media_id,
            'url' => $media?->getUrl(),
            'media' => $media ? [
                'id' => $media->id,
                'mimeType' => $media->mime_type,
                'url' => $media->getUrl(),
            ] : null,
            'caption' => $photo->caption,
            'displayOrder' => $photo->display_order,
        ];
    }
}
