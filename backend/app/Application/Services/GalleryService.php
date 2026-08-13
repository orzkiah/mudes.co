<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\GalleryPhoto;
use App\Infrastructure\Repositories\Contracts\GalleryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GalleryService extends AbstractContentService
{
    public function __construct(private readonly GalleryRepositoryInterface $galleryRepository)
    {
        parent::__construct($galleryRepository);
    }

    /**
     * @param array<int, string> $mediaIds
     * @return Collection<int, GalleryPhoto>
     */
    public function addPhotos(string $galleryId, array $mediaIds): Collection
    {
        return $this->transaction(fn () => $this->galleryRepository->addPhotos($galleryId, $mediaIds));
    }

    public function removePhoto(string $photoId): bool
    {
        return $this->transaction(function () use ($photoId) {
            $photo = $this->galleryRepository->findPhotoOrFail($photoId);

            return $this->galleryRepository->removePhoto($photo);
        });
    }

    /**
     * @param array<int, string> $orderedPhotoIds
     */
    public function reorderPhotos(array $orderedPhotoIds): void
    {
        $this->transaction(function () use ($orderedPhotoIds): void {
            $this->galleryRepository->reorderPhotos($orderedPhotoIds);
        });
    }
}
