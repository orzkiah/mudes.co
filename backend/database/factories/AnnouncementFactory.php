<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'priority' => 'normal',
            'audience' => 'public',
            'pinned' => false,
            'starts_at' => now(),
        ];
    }
}
