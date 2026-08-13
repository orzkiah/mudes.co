<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Enums\OrganizationPositionType;
use App\Domain\Models\OrganizationPeriod;
use App\Domain\Models\OrganizationPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationPosition>
 */
class OrganizationPositionFactory extends Factory
{
    protected $model = OrganizationPosition::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_period_id' => OrganizationPeriod::factory(),
            'title' => fake()->jobTitle(),
            'position_type' => OrganizationPositionType::Member->value,
            'level' => 0,
            'display_order' => 0,
        ];
    }
}
