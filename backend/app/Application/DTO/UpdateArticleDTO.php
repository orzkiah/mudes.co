<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class UpdateArticleDTO extends BaseDTO
{
    public function __construct(
        public readonly string $articleCategoryId,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $excerpt,
        public readonly string $body,
        public readonly ?string $coverMediaId,
        public readonly string $status,
        public readonly ?string $publishedAt,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{articleCategoryId: string, title: string, slug?: ?string, excerpt?: ?string, body: string, coverMediaId?: ?string, status?: ?string, publishedAt?: ?string} $validated */
        $validated = $request->validated();

        return new self(
            articleCategoryId: $validated['articleCategoryId'],
            title: $validated['title'],
            slug: $validated['slug'] ?? null,
            excerpt: $validated['excerpt'] ?? null,
            body: $validated['body'],
            coverMediaId: $validated['coverMediaId'] ?? null,
            status: $validated['status'] ?? 'draft',
            publishedAt: $validated['publishedAt'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_category_id' => $this->articleCategoryId,
            'title' => $this->title,
            'slug' => $this->slug ?? Str::slug($this->title),
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'cover_media_id' => $this->coverMediaId,
            'status' => $this->status,
            'published_at' => $this->publishedAt ?? ($this->status === 'published' ? now() : null),
        ];
    }
}
