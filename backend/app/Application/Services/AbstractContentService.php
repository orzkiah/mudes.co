<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;
use App\Shared\Support\QueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Shared CRUD orchestration for every Standard CRUD Contract content module
 * (API_SPECIFICATION.md §8.1) - Activities, Articles, Galleries, Digital
 * Library, Announcements all follow this exact create/update/delete/restore
 * shape, differing only in module-specific extras each concrete Service
 * adds on top (e.g. Articles' scheduled-publish, Galleries' photo
 * attach/remove).
 */
abstract class AbstractContentService extends BaseService
{
    public function __construct(protected readonly ContentRepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage, ?QueryFilter $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filter);
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
        return $this->transaction(fn () => $this->repository->delete($model));
    }

    public function restore(string $id): Model
    {
        return $this->transaction(function () use ($id) {
            $model = $this->repository->findTrashedOrFail($id);
            $this->repository->restore($model);

            return $model->refresh();
        });
    }
}
