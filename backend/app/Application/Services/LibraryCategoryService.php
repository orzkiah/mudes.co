<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Repositories\Contracts\LibraryCategoryRepositoryInterface;

class LibraryCategoryService extends AbstractTaxonomyService
{
    public function __construct(LibraryCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function deleteConflictMessage(): string
    {
        return 'This library category cannot be deleted while library documents still reference it.';
    }
}
