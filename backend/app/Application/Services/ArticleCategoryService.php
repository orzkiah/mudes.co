<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Repositories\Contracts\ArticleCategoryRepositoryInterface;

class ArticleCategoryService extends AbstractTaxonomyService
{
    public function __construct(ArticleCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function deleteConflictMessage(): string
    {
        return 'This article category cannot be deleted while articles still reference it.';
    }
}
