<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTO\CreateDepartmentDTO;
use App\Application\DTO\UpdateDepartmentDTO;
use App\Domain\Models\Department;
use App\Infrastructure\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Shared\Exceptions\DependencyConflictException;
use App\Shared\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService extends BaseService
{
    public function __construct(private readonly DepartmentRepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage, ?QueryFilter $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filter);
    }

    /**
     * @return Collection<int, Department>
     */
    public function listActive(): Collection
    {
        return $this->repository->allActiveOrdered();
    }

    public function find(string $id): Department
    {
        return $this->repository->findOrFail($id);
    }

    public function create(CreateDepartmentDTO $dto): Department
    {
        return $this->transaction(fn () => $this->repository->create($dto->toArray()));
    }

    public function update(Department $department, UpdateDepartmentDTO $dto): Department
    {
        return $this->transaction(fn () => $this->repository->update($department, $dto->toArray()));
    }

    public function delete(Department $department): bool
    {
        return $this->transaction(function () use ($department) {
            if ($this->repository->countPositions((string) $department->id) > 0) {
                throw new DependencyConflictException(
                    'This department cannot be deleted while positions still reference it.'
                );
            }

            return $this->repository->delete($department);
        });
    }

    public function restore(string $id): Department
    {
        return $this->transaction(function () use ($id) {
            $department = $this->repository->findTrashedOrFail($id);
            $this->repository->restore($department);

            return $department->refresh();
        });
    }

    /**
     * @param array<int, string> $ids
     */
    public function bulkActivate(array $ids): int
    {
        return $this->transaction(fn () => $this->repository->bulkSetActive($ids, true));
    }

    /**
     * @param array<int, string> $ids
     */
    public function bulkDeactivate(array $ids): int
    {
        return $this->transaction(fn () => $this->repository->bulkSetActive($ids, false));
    }

    /**
     * @param array<int, string> $ids
     */
    public function bulkDelete(array $ids): int
    {
        return $this->transaction(fn () => $this->repository->bulkDelete($ids));
    }

    /**
     * @param array<int, string> $ids
     */
    public function bulkRestore(array $ids): int
    {
        return $this->transaction(fn () => $this->repository->bulkRestore($ids));
    }

    /**
     * @param array<int, string> $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        $this->transaction(function () use ($orderedIds): void {
            $this->repository->reorder($orderedIds);
        });
    }

}
