<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\Attendance;
use App\Domain\Models\AttendanceSession;
use App\Domain\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $member = Member::factory()->create();

        return [
            'attendance_session_id' => AttendanceSession::factory(),
            'member_id' => $member->id,
            'member_name' => $member->full_name,
            'method' => 'manual',
            'checked_in_at' => now(),
        ];
    }
}
