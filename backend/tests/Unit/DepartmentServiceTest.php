<?php

declare(strict_types=1);

use App\Application\Services\DepartmentService;
use App\Domain\Models\Department;
use App\Infrastructure\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Shared\Exceptions\DependencyConflictException;
use Illuminate\Support\Str;

it('blocks deletion when positions still reference the department', function (): void {
    $department = new Department(['name' => 'Has Positions']);
    $department->id = (string) Str::uuid7();

    $repository = Mockery::mock(DepartmentRepositoryInterface::class);
    $repository->shouldReceive('countPositions')->once()->andReturn(3);
    $repository->shouldNotReceive('delete');

    $service = new DepartmentService($repository);

    expect(fn () => $service->delete($department))->toThrow(DependencyConflictException::class);
});

it('allows deletion when no positions reference the department', function (): void {
    $department = new Department(['name' => 'No Positions']);
    $department->id = (string) Str::uuid7();

    $repository = Mockery::mock(DepartmentRepositoryInterface::class);
    $repository->shouldReceive('countPositions')->once()->andReturn(0);
    $repository->shouldReceive('delete')->once()->andReturn(true);

    $service = new DepartmentService($repository);

    expect($service->delete($department))->toBeTrue();
});

it('reorders departments by delegating to the repository', function (): void {
    $repository = Mockery::mock(DepartmentRepositoryInterface::class);
    $repository->shouldReceive('reorder')->once()->with(['id-2', 'id-1']);

    $service = new DepartmentService($repository);

    $service->reorder(['id-2', 'id-1']);
});
