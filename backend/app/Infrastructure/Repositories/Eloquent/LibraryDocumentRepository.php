<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\LibraryDocument;
use App\Infrastructure\Repositories\Contracts\LibraryDocumentRepositoryInterface;

class LibraryDocumentRepository extends BaseRepository implements LibraryDocumentRepositoryInterface
{
    public function __construct(LibraryDocument $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?LibraryDocument
    {
        return LibraryDocument::query()->with(['category', 'file'])->find($id);
    }

    public function findOrFail(string $id): LibraryDocument
    {
        return LibraryDocument::query()->with(['category', 'file'])->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): LibraryDocument
    {
        return LibraryDocument::withTrashed()->with(['category', 'file'])->findOrFail($id);
    }
}
