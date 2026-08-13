<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\GalleryCategory;

interface GalleryCategoryRepositoryInterface extends TaxonomyRepositoryInterface
{
    public function find(string $id): ?GalleryCategory;

    public function findOrFail(string $id): GalleryCategory;

    public function findTrashedOrFail(string $id): GalleryCategory;
}
