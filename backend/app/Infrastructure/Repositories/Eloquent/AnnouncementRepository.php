<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Announcement;
use App\Infrastructure\Repositories\Contracts\AnnouncementRepositoryInterface;

class AnnouncementRepository extends BaseRepository implements AnnouncementRepositoryInterface
{
    public function __construct(Announcement $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?Announcement
    {
        return Announcement::query()->find($id);
    }

    public function findOrFail(string $id): Announcement
    {
        return Announcement::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): Announcement
    {
        return Announcement::withTrashed()->findOrFail($id);
    }
}
