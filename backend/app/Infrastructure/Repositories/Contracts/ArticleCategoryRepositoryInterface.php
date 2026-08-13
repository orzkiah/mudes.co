<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\ArticleCategory;

interface ArticleCategoryRepositoryInterface extends TaxonomyRepositoryInterface
{
    public function find(string $id): ?ArticleCategory;

    public function findOrFail(string $id): ArticleCategory;

    public function findTrashedOrFail(string $id): ArticleCategory;
}
