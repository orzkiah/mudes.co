<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Member;
use App\Infrastructure\Repositories\Contracts\MemberRepositoryInterface;
use App\Shared\Support\QueryFilter;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberRepository extends BaseRepository implements MemberRepositoryInterface
{
    private const WITH = ['photo', 'positions'];

    public function __construct(Member $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?Member
    {
        return Member::query()->with(self::WITH)->find($id);
    }

    public function findOrFail(string $id): Member
    {
        return Member::query()->with(self::WITH)->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): Member
    {
        return Member::withTrashed()->with(self::WITH)->findOrFail($id);
    }

    public function paginate(int $perPage = 20, ?QueryFilter $filter = null): LengthAwarePaginator
    {
        $query = Member::query()->with(self::WITH);

        if ($filter !== null) {
            $filter->apply($query);
        }

        return $query->paginate($perPage);
    }
}
