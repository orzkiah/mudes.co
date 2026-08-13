<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\ArticleCategory;

class ArticleCategoryResource extends AbstractTaxonomyResource
{
    protected function itemCountKey(): string
    {
        return 'articleCount';
    }

    protected function itemCount(): int
    {
        /** @var ArticleCategory $model */
        $model = $this->resource;

        return $model->articles()->count();
    }
}
