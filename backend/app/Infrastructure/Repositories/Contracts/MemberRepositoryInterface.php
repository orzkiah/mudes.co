<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Member;

interface MemberRepositoryInterface extends RepositoryInterface
{
    public function find(string $id): ?Member;

    public function findOrFail(string $id): Member;

    public function findTrashedOrFail(string $id): Member;
}
