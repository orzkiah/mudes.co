<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\StudyCategory;
use App\Domain\Models\StudySchedule;
use App\Infrastructure\Repositories\Contracts\StudyCategoryRepositoryInterface;

class StudyCategoryRepository extends AbstractTaxonomyRepository implements StudyCategoryRepositoryInterface
{
    public function __construct(StudyCategory $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?StudyCategory
    {
        return StudyCategory::query()->find($id);
    }

    public function findOrFail(string $id): StudyCategory
    {
        return StudyCategory::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): StudyCategory
    {
        return StudyCategory::withTrashed()->findOrFail($id);
    }

    public function bulkRestore(array $ids): int
    {
        return StudyCategory::withTrashed()->whereIn('id', $ids)->restore();
    }

    public function countReferences(string $id): int
    {
        return StudySchedule::query()->where('study_category_id', $id)->count();
    }
}
