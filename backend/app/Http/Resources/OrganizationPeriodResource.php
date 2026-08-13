<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\OrganizationPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * API_SPECIFICATION.md §9.8 — Period shape:
 * { id, label, startDate, endDate, isActive, createdAt, updatedAt }
 *
 * @property OrganizationPeriod $resource
 */
class OrganizationPeriodResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var OrganizationPeriod $period */
        $period = $this->resource;

        /** @var Carbon $startDate */
        $startDate = $period->start_date;

        /** @var Carbon $endDate */
        $endDate = $period->end_date;

        return [
            'id' => $period->id,
            'label' => $period->label,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'isActive' => $period->is_active,
            'createdAt' => $period->created_at?->toIso8601String(),
            'updatedAt' => $period->updated_at?->toIso8601String(),
        ];
    }
}
