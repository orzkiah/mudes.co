<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface extends RepositoryInterface
{
    public function find(string $id): ?Department;

    public function findOrFail(string $id): Department;

    public function findTrashedOrFail(string $id): Department;

    /**
     * Active departments, ordered for public display.
     *
     * @return Collection<int, Department>
     */
    public function allActiveOrdered(): Collection;

    /**
     * @param array<int, string> $ids
     */
    public function bulkSetActive(array $ids, bool $active): int;

    /**
     * @param array<int, string> $ids
     */
    public function bulkDelete(array $ids): int;

    /**
     * @param array<int, string> $ids
     */
    public function bulkRestore(array $ids): int;

    /**
     * @param array<int, string> $orderedIds
     */
    public function reorder(array $orderedIds): void;

    /**
     * How many organization positions currently reference this department -
     * blocks deletion when non-zero (DepartmentService::delete()).
     */
    public function countPositions(string $departmentId): int;
}
