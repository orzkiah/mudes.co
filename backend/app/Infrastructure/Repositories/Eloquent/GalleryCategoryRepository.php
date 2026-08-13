<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Gallery;
use App\Domain\Models\GalleryCategory;
use App\Infrastructure\Repositories\Contracts\GalleryCategoryRepositoryInterface;

class GalleryCategoryRepository extends AbstractTaxonomyRepository implements GalleryCategoryRepositoryInterface
{
    public function __construct(GalleryCategory $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?GalleryCategory
    {
        return GalleryCategory::query()->find($id);
    }

    public function findOrFail(string $id): GalleryCategory
    {
        return GalleryCategory::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): GalleryCategory
    {
        return GalleryCategory::withTrashed()->findOrFail($id);
    }

    public function bulkRestore(array $ids): int
    {
        return GalleryCategory::withTrashed()->whereIn('id', $ids)->restore();
    }

    public function countReferences(string $id): int
    {
        return Gallery::query()->where('gallery_category_id', $id)->count();
    }
}
