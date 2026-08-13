<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\StudyCategory;

interface StudyCategoryRepositoryInterface extends TaxonomyRepositoryInterface
{
    public function find(string $id): ?StudyCategory;

    public function findOrFail(string $id): StudyCategory;

    public function findTrashedOrFail(string $id): StudyCategory;
}
