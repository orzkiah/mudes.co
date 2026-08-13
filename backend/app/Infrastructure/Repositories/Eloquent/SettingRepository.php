<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Setting;
use App\Infrastructure\Repositories\Contracts\SettingRepositoryInterface;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?Setting
    {
        return Setting::query()->find($id);
    }

    public function findOrFail(string $id): Setting
    {
        return Setting::query()->findOrFail($id);
    }

    public function findByKey(string $key): ?Setting
    {
        return Setting::query()->where('key', $key)->first();
    }

    public function findTrashedOrFail(string $id): Setting
    {
        return Setting::withTrashed()->findOrFail($id);
    }
}
