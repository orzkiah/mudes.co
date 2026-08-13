<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Department;
use App\Domain\Models\OrganizationPosition;
use App\Infrastructure\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?Department
    {
        return Department::query()->find($id);
    }

    public function findOrFail(string $id): Department
    {
        return Department::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): Department
    {
        return Department::withTrashed()->findOrFail($id);
    }

    public function allActiveOrdered(): Collection
    {
        return Department::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    public function bulkSetActive(array $ids, bool $active): int
    {
        $count = 0;

        // Looped (not a bulk query builder update) so the Observer fires per
        // row and updated_by is tracked correctly for each affected department.
        foreach (Department::query()->whereIn('id', $ids)->get() as $department) {
            $department->is_active = $active;
            $count += (int) $department->save();
        }

        return $count;
    }

    public function bulkDelete(array $ids): int
    {
        $count = 0;

        foreach (Department::query()->whereIn('id', $ids)->get() as $department) {
            $count += (int) $department->delete();
        }

        return $count;
    }

    public function bulkRestore(array $ids): int
    {
        return Department::withTrashed()->whereIn('id', $ids)->restore();
    }

    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            foreach ($orderedIds as $position => $id) {
                Department::query()->whereKey($id)->update(['display_order' => $position]);
            }
        });
    }

    public function countPositions(string $departmentId): int
    {
        return OrganizationPosition::query()->where('department_id', $departmentId)->count();
    }
}
