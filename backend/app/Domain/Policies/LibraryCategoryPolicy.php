<?php

declare(strict_types=1);

namespace App\Domain\Policies;

class LibraryCategoryPolicy extends AbstractTaxonomyPolicy
{
    protected function permissionPrefix(): string
    {
        return 'library-categories';
    }
}
