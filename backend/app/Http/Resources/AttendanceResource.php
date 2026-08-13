<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\AttendanceMethod;
use App\Domain\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property Attendance $resource
 */
class AttendanceResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Attendance $attendance */
        $attendance = $this->resource;

        /** @var AttendanceMethod $method */
        $method = $attendance->method;

        /** @var Carbon $checkedInAt */
        $checkedInAt = $attendance->checked_in_at;

        return [
            'id' => $attendance->id,
            'attendanceSessionId' => $attendance->attendance_session_id,
            'memberId' => $attendance->member_id,
            'memberName' => $attendance->member_name,
            'method' => $method->value,
            'checkedInAt' => $checkedInAt->toIso8601String(),
        ];
    }
}
