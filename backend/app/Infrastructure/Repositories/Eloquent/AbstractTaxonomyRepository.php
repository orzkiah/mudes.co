<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Infrastructure\Repositories\Contracts\TaxonomyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Shared bulk/reorder/listActive behavior for every Taxonomy Resource
 * Contract table (API_SPECIFICATION.md §8.2). Concrete repositories only add
 * find/findOrFail/findTrashedOrFail (narrowed to their own Model, matching
 * the precedent set by SettingRepository/DepartmentRepository) and
 * countReferences().
 */
abstract class AbstractTaxonomyRepository extends BaseRepository implements TaxonomyRepositoryInterface
{
    public function allActiveOrdered(): Collection
    {
        return $this->model->newQuery()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    public function bulkSetActive(array $ids, bool $active): int
    {
        $count = 0;

        // Looped (not a bulk query builder update) so the Observer fires per
        // row and updated_by is tracked correctly for each affected row.
        foreach ($this->model->newQuery()->whereIn('id', $ids)->get() as $row) {
            $row->setAttribute('is_active', $active);
            $count += (int) $row->save();
        }

        return $count;
    }

    public function bulkDelete(array $ids): int
    {
        $count = 0;

        foreach ($this->model->newQuery()->whereIn('id', $ids)->get() as $row) {
            $count += (int) $row->delete();
        }

        return $count;
    }

    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            foreach ($orderedIds as $position => $id) {
                $this->model->newQuery()->whereKey($id)->update(['display_order' => $position]);
            }
        });
    }
}
