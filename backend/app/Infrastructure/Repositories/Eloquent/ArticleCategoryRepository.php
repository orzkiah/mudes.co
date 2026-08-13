<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Article;
use App\Domain\Models\ArticleCategory;
use App\Infrastructure\Repositories\Contracts\ArticleCategoryRepositoryInterface;

class ArticleCategoryRepository extends AbstractTaxonomyRepository implements ArticleCategoryRepositoryInterface
{
    public function __construct(ArticleCategory $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?ArticleCategory
    {
        return ArticleCategory::query()->find($id);
    }

    public function findOrFail(string $id): ArticleCategory
    {
        return ArticleCategory::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): ArticleCategory
    {
        return ArticleCategory::withTrashed()->findOrFail($id);
    }

    public function bulkRestore(array $ids): int
    {
        return ArticleCategory::withTrashed()->whereIn('id', $ids)->restore();
    }

    public function countReferences(string $id): int
    {
        return Article::query()->where('article_category_id', $id)->count();
    }
}
