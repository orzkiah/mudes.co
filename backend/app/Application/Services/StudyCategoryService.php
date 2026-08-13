<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Repositories\Contracts\StudyCategoryRepositoryInterface;

class StudyCategoryService extends AbstractTaxonomyService
{
    public function __construct(StudyCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function deleteConflictMessage(): string
    {
        return 'This study category cannot be deleted while study schedules still reference it.';
    }
}
