<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\StudyCategory;

class StudyCategoryResource extends AbstractTaxonomyResource
{
    protected function itemCountKey(): string
    {
        return 'scheduleCount';
    }

    protected function itemCount(): int
    {
        /** @var StudyCategory $model */
        $model = $this->resource;

        return $model->schedules()->count();
    }
}
