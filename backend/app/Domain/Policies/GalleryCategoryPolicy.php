<?php

declare(strict_types=1);

namespace App\Domain\Policies;

class GalleryCategoryPolicy extends AbstractTaxonomyPolicy
{
    protected function permissionPrefix(): string
    {
        return 'gallery-categories';
    }
}
