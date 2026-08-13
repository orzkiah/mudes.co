<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\GalleryCategory;

class GalleryCategoryResource extends AbstractTaxonomyResource
{
    protected function itemCountKey(): string
    {
        return 'galleryCount';
    }

    protected function itemCount(): int
    {
        /** @var GalleryCategory $model */
        $model = $this->resource;

        return $model->galleries()->count();
    }
}
