<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\OrganizationPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationPeriod>
 */
class OrganizationPeriodFactory extends Factory
{
    protected $model = OrganizationPeriod::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => 'Periode '.fake()->year().'-'.(fake()->year() + 2),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'is_active' => false,
        ];
    }
}
