<?php

declare(strict_types=1);

namespace App\Domain\Policies;

class ArticleCategoryPolicy extends AbstractTaxonomyPolicy
{
    protected function permissionPrefix(): string
    {
        return 'article-categories';
    }
}
