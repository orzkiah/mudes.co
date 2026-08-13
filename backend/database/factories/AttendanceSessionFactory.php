<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\AttendanceSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    protected $model = AttendanceSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_type' => 'activity',
            'source_id' => (string) Str::uuid7(),
            'qr_token' => Str::random(40),
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addHour(),
        ];
    }
}
