<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\ActivityCategory;

interface ActivityCategoryRepositoryInterface extends TaxonomyRepositoryInterface
{
    public function find(string $id): ?ActivityCategory;

    public function findOrFail(string $id): ActivityCategory;

    public function findTrashedOrFail(string $id): ActivityCategory;
}
