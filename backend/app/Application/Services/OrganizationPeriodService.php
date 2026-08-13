<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\OrganizationPeriod;
use App\Infrastructure\Repositories\Contracts\OrganizationPeriodRepositoryInterface;
use App\Shared\Support\QueryFilter;
use Illuminate\Pagination\LengthAwarePaginator;

class OrganizationPeriodService extends BaseService
{
    public function __construct(private readonly OrganizationPeriodRepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage, ?QueryFilter $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filter);
    }

    public function find(string $id): OrganizationPeriod
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): OrganizationPeriod
    {
        return $this->transaction(fn () => $this->repository->create($attributes));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(OrganizationPeriod $period, array $attributes): OrganizationPeriod
    {
        return $this->transaction(fn () => $this->repository->update($period, $attributes));
    }

    public function delete(OrganizationPeriod $period): bool
    {
        return $this->transaction(fn () => $this->repository->delete($period));
    }

    public function restore(string $id): OrganizationPeriod
    {
        return $this->transaction(function () use ($id) {
            $period = $this->repository->findTrashedOrFail($id);
            $this->repository->restore($period);

            return $period->refresh();
        });
    }

    /**
     * Activates the given period and deactivates any previously active period.
     *
     * The partial unique index `uq_organization_periods_active` is the DB's
     * final guarantee; this method enforces it at the Service layer first so
     * the client receives a predictable outcome rather than a DB error.
     * (BACKEND_ARCHITECTURE.md §5.5 — only one active period is allowed.)
     */
    public function activate(OrganizationPeriod $period): OrganizationPeriod
    {
        return $this->transaction(function () use ($period) {
            $this->repository->deactivateAll();
            // Refresh the model so Eloquent sees it as dirty after deactivateAll
            // updated the DB row — without this, update() on a model whose
            // in-memory is_active is already true would see no dirty attribute
            // and skip the UPDATE query.
            $period->refresh();
            $this->repository->update($period, ['is_active' => true]);

            return $period->refresh();
        });
    }
}
