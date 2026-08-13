<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\OrganizationPeriod;

interface OrganizationPeriodRepositoryInterface extends RepositoryInterface
{
    public function find(string $id): ?OrganizationPeriod;

    public function findOrFail(string $id): OrganizationPeriod;

    public function findTrashedOrFail(string $id): OrganizationPeriod;

    /**
     * Returns the currently active period, or null if none exists.
     */
    public function findActive(): ?OrganizationPeriod;

    /**
     * Deactivates all periods (sets is_active = false) — called inside the
     * activate transaction to enforce the single-active-period invariant
     * before the new period is set active.
     */
    public function deactivateAll(): void;
}
