<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\ActivityCategory;

class ActivityCategoryResource extends AbstractTaxonomyResource
{
    protected function itemCountKey(): string
    {
        return 'activityCount';
    }

    protected function itemCount(): int
    {
        /** @var ActivityCategory $model */
        $model = $this->resource;

        return $model->activities()->count();
    }
}
