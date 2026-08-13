<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\ArticleStatus;
use App\Domain\Models\Article;
use App\Domain\Models\ArticleCategory;
use App\Domain\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property Article $resource
 */
class ArticleResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Article $article */
        $article = $this->resource;

        /** @var ArticleCategory|null $category */
        $category = $article->category;

        /** @var Media|null $cover */
        $cover = $article->cover;

        /** @var ArticleStatus $status */
        $status = $article->status;

        /** @var Carbon|null $publishedAt */
        $publishedAt = $article->published_at;

        return [
            'id' => $article->id,
            'articleCategoryId' => $article->article_category_id,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'color' => $category->color,
            ] : null,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'body' => $article->body,
            'cover' => $cover ? [
                'id' => $cover->id,
                'url' => $cover->getUrl(),
                'name' => $cover->name,
            ] : null,
            'status' => $status->value,
            'publishedAt' => $publishedAt?->toIso8601String(),
            'viewCount' => $article->view_count,
            'authorId' => $article->created_by,
            'createdAt' => $article->created_at?->toIso8601String(),
            'updatedAt' => $article->updated_at?->toIso8601String(),
        ];
    }
}
