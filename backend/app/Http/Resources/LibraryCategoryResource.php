<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\LibraryCategory;

class LibraryCategoryResource extends AbstractTaxonomyResource
{
    protected function itemCountKey(): string
    {
        return 'documentCount';
    }

    protected function itemCount(): int
    {
        /** @var LibraryCategory $model */
        $model = $this->resource;

        return $model->documents()->count();
    }
}
