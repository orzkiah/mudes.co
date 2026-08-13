<?php

declare(strict_types=1);

namespace App\Domain\Policies;

class StudyCategoryPolicy extends AbstractTaxonomyPolicy
{
    protected function permissionPrefix(): string
    {
        return 'study-categories';
    }
}
