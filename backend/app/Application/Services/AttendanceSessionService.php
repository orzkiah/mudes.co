<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\Attendance;
use App\Domain\Models\AttendanceSession;
use App\Domain\Models\Member;
use App\Infrastructure\Repositories\Contracts\AttendanceSessionRepositoryInterface;
use App\Shared\Exceptions\AttendanceWindowClosedException;
use App\Shared\Exceptions\DuplicateCheckInException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class AttendanceSessionService extends AbstractContentService
{
    public function __construct(private readonly AttendanceSessionRepositoryInterface $sessionRepository)
    {
        parent::__construct($sessionRepository);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): AttendanceSession
    {
        $attributes['qr_token'] ??= (string) Str::random(40);

        /** @var AttendanceSession */
        return parent::create($attributes);
    }

    public function find(string $id): AttendanceSession
    {
        /** @var AttendanceSession */
        return parent::find($id);
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function roster(string $sessionId): Collection
    {
        return $this->sessionRepository->rosterFor($sessionId);
    }

    /**
     * Public, token-scoped self check-in
     * (PROJECT_SPECIFICATION.md §15 - "requires no login").
     */
    public function checkInByToken(string $qrToken, string $memberId): Attendance
    {
        return $this->transaction(function () use ($qrToken, $memberId) {
            $session = $this->sessionRepository->findByToken($qrToken);

            if ($session === null) {
                abort(404);
            }

            if (! $session->isOpenAt(now())) {
                throw new AttendanceWindowClosedException('This attendance session is not currently open for check-in.');
            }

            return $this->recordCheckIn($session, $memberId, 'qr');
        });
    }

    /**
     * Manual check-in from the session's live check-in screen
     * (PROJECT_SPECIFICATION.md §15 - Admin/Sekretaris only).
     */
    public function manualCheckIn(AttendanceSession $session, ?string $memberId, ?string $memberName): Attendance
    {
        return $this->transaction(function () use ($session, $memberId, $memberName) {
            if ($memberId !== null) {
                return $this->recordCheckIn($session, $memberId, 'manual');
            }

            return $this->sessionRepository->createAttendance([
                'attendance_session_id' => $session->id,
                'member_id' => null,
                'member_name' => $memberName,
                'method' => 'manual',
                'checked_in_at' => now(),
            ]);
        });
    }

    private function recordCheckIn(AttendanceSession $session, string $memberId, string $method): Attendance
    {
        if ($this->sessionRepository->findAttendanceForMember((string) $session->id, $memberId) !== null) {
            throw new DuplicateCheckInException('This member has already checked into this session.');
        }

        $member = Member::query()->findOrFail($memberId);

        return $this->sessionRepository->createAttendance([
            'attendance_session_id' => $session->id,
            'member_id' => $member->id,
            'member_name' => $member->full_name,
            'method' => $method,
            'checked_in_at' => now(),
        ]);
    }
}
