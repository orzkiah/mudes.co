<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateLibraryDocumentDTO extends BaseDTO
{
    public function __construct(
        public readonly string $libraryCategoryId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $fileMediaId,
        public readonly ?string $externalUrl,
        public readonly string $visibility,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{libraryCategoryId: string, title: string, description?: ?string, fileMediaId?: ?string, externalUrl?: ?string, visibility?: ?string} $validated */
        $validated = $request->validated();

        return new self(
            libraryCategoryId: $validated['libraryCategoryId'],
            title: $validated['title'],
            description: $validated['description'] ?? null,
            fileMediaId: $validated['fileMediaId'] ?? null,
            externalUrl: $validated['externalUrl'] ?? null,
            visibility: $validated['visibility'] ?? 'internal',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'library_category_id' => $this->libraryCategoryId,
            'title' => $this->title,
            'description' => $this->description,
            'file_media_id' => $this->fileMediaId,
            'external_url' => $this->externalUrl,
            'visibility' => $this->visibility,
        ];
    }
}
