<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Announcement;

interface AnnouncementRepositoryInterface extends ContentRepositoryInterface
{
    public function find(string $id): ?Announcement;

    public function findOrFail(string $id): Announcement;

    public function findTrashedOrFail(string $id): Announcement;
}
