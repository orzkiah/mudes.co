<?php

declare(strict_types=1);

use App\Application\DTO\CreateSettingDTO;
use App\Application\Services\SettingService;
use App\Domain\Models\Setting;
use App\Infrastructure\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Support\Facades\Crypt;

it('encrypts the value before persisting when isEncrypted is true', function (): void {
    $repository = Mockery::mock(SettingRepositoryInterface::class);
    $repository->shouldReceive('create')
        ->once()
        ->withArgs(function (array $attributes): bool {
            return $attributes['key'] === 'secret.key'
                && $attributes['value'] !== 'plain-secret'
                && Crypt::decryptString($attributes['value']) === 'plain-secret';
        })
        ->andReturn(new Setting());

    $service = new SettingService($repository);

    $dto = new CreateSettingDTO(
        key: 'secret.key',
        value: 'plain-secret',
        type: 'encrypted',
        group: null,
        description: null,
        isEncrypted: true,
    );

    $service->create($dto);
});

it('does not encrypt the value when isEncrypted is false', function (): void {
    $repository = Mockery::mock(SettingRepositoryInterface::class);
    $repository->shouldReceive('create')
        ->once()
        ->withArgs(fn (array $attributes): bool => $attributes['value'] === 'plain-value')
        ->andReturn(new Setting());

    $service = new SettingService($repository);

    $dto = new CreateSettingDTO(
        key: 'plain.key',
        value: 'plain-value',
        type: 'string',
        group: null,
        description: null,
        isEncrypted: false,
    );

    $service->create($dto);
});

it('leaves a null value untouched regardless of isEncrypted', function (): void {
    $repository = Mockery::mock(SettingRepositoryInterface::class);
    $repository->shouldReceive('create')
        ->once()
        ->withArgs(fn (array $attributes): bool => $attributes['value'] === null)
        ->andReturn(new Setting());

    $service = new SettingService($repository);

    $dto = new CreateSettingDTO(
        key: 'null.key',
        value: null,
        type: 'string',
        group: null,
        description: null,
        isEncrypted: true,
    );

    $service->create($dto);
});
