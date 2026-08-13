<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Activity;

interface ActivityRepositoryInterface extends ContentRepositoryInterface
{
    public function find(string $id): ?Activity;

    public function findOrFail(string $id): Activity;

    public function findBySlug(string $slug): ?Activity;

    public function findTrashedOrFail(string $id): Activity;
}
