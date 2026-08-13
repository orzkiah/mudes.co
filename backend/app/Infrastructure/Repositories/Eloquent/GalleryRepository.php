<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Gallery;
use App\Domain\Models\GalleryPhoto;
use App\Infrastructure\Repositories\Contracts\GalleryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GalleryRepository extends BaseRepository implements GalleryRepositoryInterface
{
    private const WITH = ['category', 'activity', 'coverPhoto', 'photos.media'];

    public function __construct(Gallery $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?Gallery
    {
        return Gallery::query()->with(self::WITH)->find($id);
    }

    public function findOrFail(string $id): Gallery
    {
        return Gallery::query()->with(self::WITH)->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): Gallery
    {
        return Gallery::withTrashed()->with(self::WITH)->findOrFail($id);
    }

    public function addPhotos(string $galleryId, array $mediaIds): Collection
    {
        $startOrder = (int) GalleryPhoto::query()->where('gallery_id', $galleryId)->max('display_order');

        $created = new Collection();

        foreach (array_values($mediaIds) as $index => $mediaId) {
            $created->push(GalleryPhoto::query()->create([
                'gallery_id' => $galleryId,
                'media_id' => $mediaId,
                'display_order' => $startOrder + $index + 1,
            ]));
        }

        return $created;
    }

    public function findPhotoOrFail(string $photoId): GalleryPhoto
    {
        return GalleryPhoto::query()->findOrFail($photoId);
    }

    public function removePhoto(GalleryPhoto $photo): bool
    {
        return (bool) $photo->delete();
    }

    public function reorderPhotos(array $orderedPhotoIds): void
    {
        DB::transaction(function () use ($orderedPhotoIds): void {
            foreach ($orderedPhotoIds as $position => $id) {
                GalleryPhoto::query()->whereKey($id)->update(['display_order' => $position]);
            }
        });
    }
}
