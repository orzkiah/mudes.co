<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Shared\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Every module Repository implements this contract. Services depend on this
 * interface, never on the concrete Eloquent implementation
 * (BACKEND_ARCHITECTURE.md §6, IMPLEMENTATION_RULES.md §6).
 */
interface RepositoryInterface
{
    public function find(string $id): ?Model;

    public function findOrFail(string $id): Model;

    /**
     * @return Collection<int, Model>
     */
    public function all(): Collection;

    public function paginate(int $perPage = 20, ?QueryFilter $filter = null): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Model;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;

    public function restore(Model $model): bool;
}
