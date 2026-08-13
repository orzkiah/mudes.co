<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\LibraryCategory;
use App\Domain\Models\LibraryDocument;
use App\Infrastructure\Repositories\Contracts\LibraryCategoryRepositoryInterface;

class LibraryCategoryRepository extends AbstractTaxonomyRepository implements LibraryCategoryRepositoryInterface
{
    public function __construct(LibraryCategory $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?LibraryCategory
    {
        return LibraryCategory::query()->find($id);
    }

    public function findOrFail(string $id): LibraryCategory
    {
        return LibraryCategory::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): LibraryCategory
    {
        return LibraryCategory::withTrashed()->findOrFail($id);
    }

    public function bulkRestore(array $ids): int
    {
        return LibraryCategory::withTrashed()->whereIn('id', $ids)->restore();
    }

    public function countReferences(string $id): int
    {
        return LibraryDocument::query()->where('library_category_id', $id)->count();
    }
}
