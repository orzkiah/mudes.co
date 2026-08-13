<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Infrastructure\Repositories\Contracts\RepositoryInterface;
use App\Shared\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent-backed implementation of the common CRUD surface. This is the
 * only layer permitted to touch the Eloquent query builder
 * (IMPLEMENTATION_RULES.md §6). Concrete Repositories extend this for the
 * common cases and add their own domain-specific query methods.
 */
abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(protected Model $model)
    {
    }

    public function find(string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(string $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function paginate(int $perPage = 20, ?QueryFilter $filter = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($filter !== null) {
            $filter->apply($query);
        }

        return $query->paginate($perPage);
    }

    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->update($attributes);

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function restore(Model $model): bool
    {
        if (! method_exists($model, 'restore')) {
            return false;
        }

        return (bool) $model->restore();
    }
}
