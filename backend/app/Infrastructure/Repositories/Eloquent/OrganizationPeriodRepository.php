<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\OrganizationPeriod;
use App\Infrastructure\Repositories\Contracts\OrganizationPeriodRepositoryInterface;

class OrganizationPeriodRepository extends BaseRepository implements OrganizationPeriodRepositoryInterface
{
    public function __construct(OrganizationPeriod $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?OrganizationPeriod
    {
        return OrganizationPeriod::query()->find($id);
    }

    public function findOrFail(string $id): OrganizationPeriod
    {
        return OrganizationPeriod::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): OrganizationPeriod
    {
        return OrganizationPeriod::withTrashed()->findOrFail($id);
    }

    public function findActive(): ?OrganizationPeriod
    {
        return OrganizationPeriod::query()->where('is_active', true)->first();
    }

    public function deactivateAll(): void
    {
        OrganizationPeriod::query()->where('is_active', true)->update(['is_active' => false]);
    }
}
