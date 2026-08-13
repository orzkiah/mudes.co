<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Activity;
use App\Domain\Models\ActivityCategory;
use App\Infrastructure\Repositories\Contracts\ActivityCategoryRepositoryInterface;

class ActivityCategoryRepository extends AbstractTaxonomyRepository implements ActivityCategoryRepositoryInterface
{
    public function __construct(ActivityCategory $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?ActivityCategory
    {
        return ActivityCategory::query()->find($id);
    }

    public function findOrFail(string $id): ActivityCategory
    {
        return ActivityCategory::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): ActivityCategory
    {
        return ActivityCategory::withTrashed()->findOrFail($id);
    }

    public function bulkRestore(array $ids): int
    {
        return ActivityCategory::withTrashed()->whereIn('id', $ids)->restore();
    }

    public function countReferences(string $id): int
    {
        return Activity::query()->where('activity_category_id', $id)->count();
    }
}
