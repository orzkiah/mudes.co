<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Repositories\Contracts\TaxonomyRepositoryInterface;
use App\Shared\Exceptions\DependencyConflictException;
use App\Shared\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Shared CRUD + bulk + reorder orchestration for every Taxonomy Resource
 * Contract module (API_SPECIFICATION.md §8.2). Concrete Services only need a
 * constructor typing the concrete Repository interface and
 * deleteConflictMessage() - matches the DependencyConflictException pattern
 * already proven in DepartmentService.
 */
abstract class AbstractTaxonomyService extends BaseService
{
    public function __construct(protected readonly TaxonomyRepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage, ?QueryFilter $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filter);
    }

    /**
     * @return Collection<int, Model>
     */
    public function listActive(): Collection
    {
        return $this->repository->allActiveOrdered();
    }

    public function find(string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Model
    {
        return $this->transaction(fn () => $this->repository->create($attributes));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Model $model, array $attributes): Model
    {
        return $this->transaction(fn () => $this->repository->update($model, $attributes));
    }

    public function delete(Model $model): bool
    {
        return $this->transaction(function () use ($model) {
            if ($this->repository->countReferences((string) $model->getKey()) > 0) {
                throw new DependencyConflictException($this->deleteConflictMessage());
            }

            return $this->repository->delete($model);
        });
    }

    public function restore(string $id): Model
    {
        return $this->transaction(function () use ($id) {
            $model = $this->repository->findTrashedOrFail($id);
            $this->repository->restore($model);

            return $model->refresh();
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

    abstract protected function deleteConflictMessage(): string;
}
