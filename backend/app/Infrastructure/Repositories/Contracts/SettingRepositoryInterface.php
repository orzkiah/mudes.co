<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Setting;

interface SettingRepositoryInterface extends RepositoryInterface
{
    public function find(string $id): ?Setting;

    public function findOrFail(string $id): Setting;

    public function findByKey(string $key): ?Setting;

    public function findTrashedOrFail(string $id): Setting;
}
