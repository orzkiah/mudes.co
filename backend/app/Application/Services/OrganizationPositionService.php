<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTO\CreateOrganizationPositionDTO;
use App\Application\DTO\UpdateOrganizationPositionDTO;
use App\Domain\Models\OrganizationPosition;
use App\Infrastructure\Repositories\Contracts\OrganizationPositionRepositoryInterface;
use App\Shared\Exceptions\DependencyConflictException;
use App\Shared\Exceptions\OrganizationPositionCycleException;
use App\Shared\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Owns the hierarchy invariants the DB cannot express
 * (BACKEND_ARCHITECTURE.md §4.8, §20.1): cycle prevention and the
 * denormalized `level` recompute on re-parenting.
 */
class OrganizationPositionService extends BaseService
{
    public function __construct(private readonly OrganizationPositionRepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage, ?QueryFilter $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filter);
    }

    public function find(string $id): OrganizationPosition
    {
        return $this->repository->findOrFail($id);
    }

    public function create(CreateOrganizationPositionDTO $dto): OrganizationPosition
    {
        return $this->transaction(function () use ($dto) {
            $attributes = $dto->toArray();
            $attributes['level'] = $this->levelFor($dto->parentPositionId);

            return $this->repository->create($attributes);
        });
    }

    public function update(OrganizationPosition $position, UpdateOrganizationPositionDTO $dto): OrganizationPosition
    {
        return $this->transaction(function () use ($position, $dto) {
            $originalParentId = $position->parent_position_id;
            $parentChanged = $dto->parentPositionId !== $originalParentId;

            if ($parentChanged) {
                $this->assertNoCycle((string) $position->id, $dto->parentPositionId);
            }

            $attributes = $dto->toArray();
            $attributes['level'] = $this->levelFor($dto->parentPositionId);

            $updated = $this->repository->update($position, $attributes);

            if ($parentChanged) {
                $this->recomputeSubtreeLevels($updated);
            }

            return $updated;
        });
    }

    public function delete(OrganizationPosition $position): bool
    {
        return $this->transaction(function () use ($position) {
            if ($this->repository->hasChildren((string) $position->id)) {
                throw new DependencyConflictException(
                    'Jabatan ini tidak dapat dihapus karena masih memiliki jabatan bawahan di bawahnya. Harap hapus atau ubah atasan dari jabatan bawahan terlebih dahulu.'
                );
            }

            return $this->repository->delete($position);
        });
    }

    public function restore(string $id): OrganizationPosition
    {
        return $this->transaction(function () use ($id) {
            $position = $this->repository->findTrashedOrFail($id);
            $this->repository->restore($position);

            return $position->refresh();
        });
    }

    /**
     * @return array{position: OrganizationPosition, affectedDescendantCount: int}
     */
    public function reorder(
        OrganizationPosition $position,
        int $displayOrder,
        ?string $parentPositionId,
        ?string $departmentId,
    ): array {
        return $this->transaction(function () use ($position, $displayOrder, $parentPositionId, $departmentId) {
            $parentChanged = $parentPositionId !== $position->parent_position_id;

            if ($parentChanged) {
                $this->assertNoCycle((string) $position->id, $parentPositionId);
            }

            $position->display_order = $displayOrder;
            $position->parent_position_id = $parentPositionId;
            $position->department_id = $departmentId;
            $position->level = $this->levelFor($parentPositionId);
            $position->save();

            $affectedDescendantCount = $parentChanged ? $this->recomputeSubtreeLevels($position) : 0;

            return [
                'position' => $position->refresh(),
                'affectedDescendantCount' => $affectedDescendantCount,
            ];
        });
    }

    /**
     * Nested tree (via in-memory `children` relations) for every position in
     * a period, built from one flat query.
     *
     * @return Collection<int, OrganizationPosition>
     */
    public function tree(string $periodId): Collection
    {
        return $this->attachChildren($this->repository->forPeriod($periodId), null);
    }

    /**
     * @return Collection<int, OrganizationPosition>
     */
    public function activePeriodTree(): Collection
    {
        $periodId = $this->repository->activePeriodId();

        if ($periodId === null) {
            return new Collection();
        }

        return $this->tree($periodId);
    }

    private function levelFor(?string $parentPositionId): int
    {
        if ($parentPositionId === null) {
            return 0;
        }

        return $this->repository->findOrFail($parentPositionId)->level + 1;
    }

    private function assertNoCycle(string $positionId, ?string $candidateParentId): void
    {
        if ($candidateParentId === null) {
            return;
        }

        if ($candidateParentId === $positionId) {
            throw new OrganizationPositionCycleException('A position cannot be its own parent.');
        }

        $descendantIds = $this->repository->descendantsOf($positionId)->pluck('id');

        if ($descendantIds->contains($candidateParentId)) {
            throw new OrganizationPositionCycleException('A position cannot be moved under one of its own descendants.');
        }
    }

    /**
     * Breadth-first recompute of `level` for every descendant of $root, whose
     * own `level` has already been updated by the caller.
     */
    private function recomputeSubtreeLevels(OrganizationPosition $root): int
    {
        $affected = 0;
        /** @var array<string, int> $frontier */
        $frontier = [(string) $root->id => $root->level];

        while ($frontier !== []) {
            $nextFrontier = [];

            foreach ($frontier as $parentId => $parentLevel) {
                foreach ($this->repository->directChildrenOf($parentId) as $child) {
                    $child->level = $parentLevel + 1;
                    $child->save();
                    $affected++;
                    $nextFrontier[(string) $child->id] = $child->level;
                }
            }

            $frontier = $nextFrontier;
        }

        return $affected;
    }

    /**
     * @param Collection<int, OrganizationPosition> $positions
     * @return Collection<int, OrganizationPosition>
     */
    private function attachChildren(Collection $positions, ?string $parentId): Collection
    {
        return $positions
            ->filter(fn (OrganizationPosition $position) => $position->parent_position_id === $parentId)
            ->values()
            ->map(function (OrganizationPosition $position) use ($positions) {
                $position->setRelation('children', $this->attachChildren($positions, (string) $position->id));

                return $position;
            });
    }
}
