<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\LibraryDocument;

interface LibraryDocumentRepositoryInterface extends ContentRepositoryInterface
{
    public function find(string $id): ?LibraryDocument;

    public function findOrFail(string $id): LibraryDocument;

    public function findTrashedOrFail(string $id): LibraryDocument;
}
