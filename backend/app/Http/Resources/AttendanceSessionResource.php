<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\AttendanceSessionSourceType;
use App\Domain\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property AttendanceSession $resource
 */
class AttendanceSessionResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AttendanceSession $session */
        $session = $this->resource;

        /** @var AttendanceSessionSourceType $sourceType */
        $sourceType = $session->source_type;

        /** @var Carbon $opensAt */
        $opensAt = $session->opens_at;

        /** @var Carbon $closesAt */
        $closesAt = $session->closes_at;

        return [
            'id' => $session->id,
            'sourceType' => $sourceType->value,
            'sourceId' => $session->source_id,
            'qrToken' => $session->qr_token,
            'opensAt' => $opensAt->toIso8601String(),
            'closesAt' => $closesAt->toIso8601String(),
            'attendanceCount' => $session->attendances()->count(),
            'createdAt' => $session->created_at?->toIso8601String(),
            'updatedAt' => $session->updated_at?->toIso8601String(),
        ];
    }
}
