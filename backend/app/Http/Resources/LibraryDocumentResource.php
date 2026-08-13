<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\LibraryDocumentVisibility;
use App\Domain\Models\LibraryCategory;
use App\Domain\Models\LibraryDocument;
use App\Domain\Models\Media;
use Illuminate\Http\Request;

/**
 * @property LibraryDocument $resource
 */
class LibraryDocumentResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var LibraryDocument $document */
        $document = $this->resource;

        /** @var LibraryCategory|null $category */
        $category = $document->category;

        /** @var Media|null $file */
        $file = $document->file;

        /** @var LibraryDocumentVisibility $visibility */
        $visibility = $document->visibility;

        return [
            'id' => $document->id,
            'libraryCategoryId' => $document->library_category_id,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'color' => $category->color,
            ] : null,
            'title' => $document->title,
            'description' => $document->description,
            'file' => $file ? [
                'id' => $file->id,
                'url' => $file->getUrl(),
                'name' => $file->name,
                'mimeType' => $file->mime_type,
            ] : null,
            'externalUrl' => $document->external_url,
            'visibility' => $visibility->value,
            'downloadCount' => $document->download_count,
            'libraryType' => $this->libraryType($file, $document->external_url),
            'createdAt' => $document->created_at?->toIso8601String(),
            'updatedAt' => $document->updated_at?->toIso8601String(),
        ];
    }

    private function libraryType(?Media $file, ?string $externalUrl): string
    {
        if ($externalUrl !== null) {
            return 'video_link';
        }

        $mimeType = $file !== null ? $file->mime_type : '';

        return str_starts_with($mimeType, 'audio/') ? 'audio' : 'pdf';
    }
}
