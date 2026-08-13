<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Enums\MemberGender;
use App\Domain\Enums\MemberStatus;
use App\Domain\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(MemberGender::cases())->value,
            'birth_date' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'join_date' => fake()->date(),
            'status' => MemberStatus::Active->value,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
