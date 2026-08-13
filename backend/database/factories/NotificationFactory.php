<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\Notification;
use App\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'type' => 'App\\Notifications\\SystemNotice',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'data' => ['message' => fake()->sentence()],
            'read_at' => null,
        ];
    }
}
