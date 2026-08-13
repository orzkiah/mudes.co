<?php

declare(strict_types=1);

namespace App\Domain\Policies;

class ActivityCategoryPolicy extends AbstractTaxonomyPolicy
{
    protected function permissionPrefix(): string
    {
        return 'activity-categories';
    }
}
