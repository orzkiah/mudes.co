<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Attendance;
use App\Domain\Models\AttendanceSession;
use App\Infrastructure\Repositories\Contracts\AttendanceSessionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendanceSessionRepository extends BaseRepository implements AttendanceSessionRepositoryInterface
{
    public function __construct(AttendanceSession $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?AttendanceSession
    {
        return AttendanceSession::query()->find($id);
    }

    public function findOrFail(string $id): AttendanceSession
    {
        return AttendanceSession::query()->findOrFail($id);
    }

    public function findTrashedOrFail(string $id): AttendanceSession
    {
        return AttendanceSession::withTrashed()->findOrFail($id);
    }

    public function findByToken(string $qrToken): ?AttendanceSession
    {
        return AttendanceSession::query()->where('qr_token', $qrToken)->first();
    }

    public function rosterFor(string $sessionId): Collection
    {
        return Attendance::query()
            ->where('attendance_session_id', $sessionId)
            ->orderBy('checked_in_at')
            ->get();
    }

    public function findAttendanceForMember(string $sessionId, string $memberId): ?Attendance
    {
        return Attendance::query()
            ->where('attendance_session_id', $sessionId)
            ->where('member_id', $memberId)
            ->first();
    }

    public function createAttendance(array $attributes): Attendance
    {
        return Attendance::query()->create($attributes);
    }
}
