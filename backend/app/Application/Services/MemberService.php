<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTO\CreateMemberDTO;
use App\Application\DTO\UpdateMemberDTO;
use App\Domain\Models\Member;
use App\Infrastructure\Repositories\Contracts\MemberRepositoryInterface;
use App\Shared\Support\QueryFilter;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberService extends BaseService
{
    public function __construct(private readonly MemberRepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage, ?QueryFilter $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filter);
    }

    public function find(string $id): Member
    {
        return $this->repository->findOrFail($id);
    }

    public function create(CreateMemberDTO $dto): Member
    {
        return $this->transaction(fn () => $this->repository->create($dto->toArray()));
    }

    public function update(Member $member, UpdateMemberDTO $dto): Member
    {
        return $this->transaction(fn () => $this->repository->update($member, $dto->toArray()));
    }

    public function delete(Member $member): bool
    {
        return $this->transaction(fn () => $this->repository->delete($member));
    }

    public function restore(string $id): Member
    {
        return $this->transaction(function () use ($id) {
            $member = $this->repository->findTrashedOrFail($id);
            $this->repository->restore($member);

            return $member->refresh();
        });
    }
}
