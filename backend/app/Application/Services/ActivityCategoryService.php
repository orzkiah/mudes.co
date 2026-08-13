<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Repositories\Contracts\ActivityCategoryRepositoryInterface;

class ActivityCategoryService extends AbstractTaxonomyService
{
    public function __construct(ActivityCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function deleteConflictMessage(): string
    {
        return 'This activity category cannot be deleted while activities still reference it.';
    }
}
