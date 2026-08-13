<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared contract for the six Taxonomy Resource Contract tables
 * (API_SPECIFICATION.md §8.2). Each concrete module's own interface extends
 * this and narrows find/findOrFail/findTrashedOrFail to its own Model.
 */
interface TaxonomyRepositoryInterface extends RepositoryInterface
{
    public function findTrashedOrFail(string $id): Model;

    /**
     * Active rows, ordered for public display.
     *
     * @return Collection<int, Model>
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
     * How many content rows currently reference this row - blocks deletion
     * when non-zero (AbstractTaxonomyService::delete()).
     */
    public function countReferences(string $id): int;
}
