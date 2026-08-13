<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\OrganizationPeriod;
use App\Domain\Models\OrganizationPosition;
use App\Infrastructure\Repositories\Contracts\OrganizationPositionRepositoryInterface;
use App\Shared\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OrganizationPositionRepository extends BaseRepository implements OrganizationPositionRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    private const WITH = ['department', 'member.photo', 'parent'];

    public function __construct(OrganizationPosition $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?OrganizationPosition
    {
        return OrganizationPosition::query()->with(self::WITH)->find($id);
    }

    public function findOrFail(string $id): OrganizationPosition
    {
        return OrganizationPosition::query()->with(self::WITH)->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): OrganizationPosition
    {
        return OrganizationPosition::withTrashed()->with(self::WITH)->findOrFail($id);
    }

    public function paginate(int $perPage = 20, ?QueryFilter $filter = null): LengthAwarePaginator
    {
        $query = OrganizationPosition::query()->with(self::WITH);

        if ($filter !== null) {
            $filter->apply($query);
        }

        return $query->paginate($perPage);
    }

    public function create(array $attributes): OrganizationPosition
    {
        return OrganizationPosition::query()->create($attributes);
    }

    public function update(Model $model, array $attributes): OrganizationPosition
    {
        $model->update($attributes);

        /** @var OrganizationPosition $model */
        return $model->refresh();
    }

    public function hasChildren(string $id): bool
    {
        return OrganizationPosition::query()->where('parent_position_id', $id)->exists();
    }

    public function descendantsOf(string $id): Collection
    {
        /** @var Collection<int, OrganizationPosition> $descendants */
        $descendants = new Collection();
        $currentIds = [$id];

        while ($currentIds !== []) {
            $children = OrganizationPosition::query()->whereIn('parent_position_id', $currentIds)->get();

            if ($children->isEmpty()) {
                break;
            }

            $descendants = $descendants->merge($children);
            $currentIds = $children->pluck('id')->all();
        }

        return $descendants;
    }

    public function directChildrenOf(string $id): Collection
    {
        return OrganizationPosition::query()->where('parent_position_id', $id)->get();
    }

    public function forPeriod(string $periodId): Collection
    {
        return OrganizationPosition::query()
            ->with(self::WITH)
            ->where('organization_period_id', $periodId)
            ->orderBy('display_order')
            ->get();
    }

    public function activePeriodId(): ?string
    {
        /** @var string|null $id */
        $id = OrganizationPeriod::query()->where('is_active', true)->value('id');

        return $id;
    }
}
