<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Gallery;
use App\Domain\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Collection;

interface GalleryRepositoryInterface extends ContentRepositoryInterface
{
    public function find(string $id): ?Gallery;

    public function findOrFail(string $id): Gallery;

    public function findTrashedOrFail(string $id): Gallery;

    /**
     * @param array<int, string> $mediaIds
     * @return Collection<int, GalleryPhoto>
     */
    public function addPhotos(string $galleryId, array $mediaIds): Collection;

    public function findPhotoOrFail(string $photoId): GalleryPhoto;

    public function removePhoto(GalleryPhoto $photo): bool;

    /**
     * @param array<int, string> $orderedPhotoIds
     */
    public function reorderPhotos(array $orderedPhotoIds): void;
}
