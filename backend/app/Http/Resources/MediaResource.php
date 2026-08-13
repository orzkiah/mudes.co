<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\Media;
use Illuminate\Http\Request;

/**
 * Serialises a Media row into the MediaObject shape used across every
 * entity resource that embeds media (API_SPECIFICATION.md §5, §9.5, §9.15).
 *
 * Shape: { id, url, name, mimeType, size, collection, createdAt }
 *
 * @property Media $resource
 */
class MediaResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Media $media */
        $media = $this->resource;

        return [
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->name,
            'fileName' => $media->file_name,
            'mimeType' => $media->mime_type,
            'size' => $media->size,
            'collection' => $media->collection_name,
            'createdAt' => $media->created_at?->toIso8601String(),
        ];
    }
}
