<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\OrganizationPosition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface OrganizationPositionRepositoryInterface extends RepositoryInterface
{
    public function find(string $id): ?OrganizationPosition;

    public function findOrFail(string $id): OrganizationPosition;

    public function findTrashedOrFail(string $id): OrganizationPosition;

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): OrganizationPosition;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Model $model, array $attributes): OrganizationPosition;

    public function hasChildren(string $id): bool;

    /**
     * All descendants of the given position, as a flat collection - used to
     * detect whether a candidate parent is actually one of the position's
     * own descendants (which would create a cycle).
     *
     * @return Collection<int, OrganizationPosition>
     */
    public function descendantsOf(string $id): Collection;

    /**
     * @return Collection<int, OrganizationPosition>
     */
    public function directChildrenOf(string $id): Collection;

    /**
     * Every position in a period, flat, ordered for tree assembly.
     *
     * @return Collection<int, OrganizationPosition>
     */
    public function forPeriod(string $periodId): Collection;

    /**
     * The currently active organization period, if any (DATABASE_SPECIFICATION.md
     * §4.7 - at most one row can have is_active = true).
     */
    public function activePeriodId(): ?string;
}
