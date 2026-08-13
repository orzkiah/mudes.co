<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTO\CreateSettingDTO;
use App\Application\DTO\UpdateSettingDTO;
use App\Domain\Models\Setting;
use App\Infrastructure\Repositories\Contracts\SettingRepositoryInterface;
use App\Shared\Support\QueryFilter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;

class SettingService extends BaseService
{
    public function __construct(private readonly SettingRepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage, ?QueryFilter $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filter);
    }

    public function find(string $id): Setting
    {
        return $this->repository->findOrFail($id);
    }

    public function create(CreateSettingDTO $dto): Setting
    {
        return $this->transaction(function () use ($dto) {
            $attributes = $dto->toArray();
            $attributes['value'] = $this->encryptIfNeeded($attributes['value'], (bool) $attributes['is_encrypted']);

            return $this->repository->create($attributes);
        });
    }

    public function update(Setting $setting, UpdateSettingDTO $dto): Setting
    {
        return $this->transaction(function () use ($setting, $dto) {
            $attributes = $dto->toArray();
            $attributes['value'] = $this->encryptIfNeeded($attributes['value'], (bool) $attributes['is_encrypted']);

            return $this->repository->update($setting, $attributes);
        });
    }

    public function delete(Setting $setting): bool
    {
        return $this->transaction(fn () => $this->repository->delete($setting));
    }

    public function restore(string $id): Setting
    {
        return $this->transaction(function () use ($id) {
            $setting = $this->repository->findTrashedOrFail($id);
            $this->repository->restore($setting);

            return $setting->refresh();
        });
    }

    private function encryptIfNeeded(?string $value, bool $isEncrypted): ?string
    {
        if ($value === null || ! $isEncrypted) {
            return $value;
        }

        return Crypt::encryptString($value);
    }
}
