<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Attendance;
use App\Domain\Models\AttendanceSession;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceSessionRepositoryInterface extends ContentRepositoryInterface
{
    public function find(string $id): ?AttendanceSession;

    public function findOrFail(string $id): AttendanceSession;

    public function findTrashedOrFail(string $id): AttendanceSession;

    public function findByToken(string $qrToken): ?AttendanceSession;

    /**
     * @return Collection<int, Attendance>
     */
    public function rosterFor(string $sessionId): Collection;

    public function findAttendanceForMember(string $sessionId, string $memberId): ?Attendance;

    /**
     * @param array<string, mixed> $attributes
     */
    public function createAttendance(array $attributes): Attendance;
}
