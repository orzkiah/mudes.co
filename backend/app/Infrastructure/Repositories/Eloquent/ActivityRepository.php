<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Activity;
use App\Infrastructure\Repositories\Contracts\ActivityRepositoryInterface;

class ActivityRepository extends BaseRepository implements ActivityRepositoryInterface
{
    public function __construct(Activity $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?Activity
    {
        return Activity::query()->with(['category', 'cover'])->find($id);
    }

    public function findOrFail(string $id): Activity
    {
        return Activity::query()->with(['category', 'cover'])->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Activity
    {
        return Activity::query()->with(['category', 'cover'])->where('slug', $slug)->first();
    }

    public function findTrashedOrFail(string $id): Activity
    {
        return Activity::withTrashed()->with(['category', 'cover'])->findOrFail($id);
    }
}
