<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\LibraryCategory;

interface LibraryCategoryRepositoryInterface extends TaxonomyRepositoryInterface
{
    public function find(string $id): ?LibraryCategory;

    public function findOrFail(string $id): LibraryCategory;

    public function findTrashedOrFail(string $id): LibraryCategory;
}
