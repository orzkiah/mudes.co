<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\AnnouncementAudience;
use App\Domain\Enums\AnnouncementPriority;
use App\Domain\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property Announcement $resource
 */
class AnnouncementResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Announcement $announcement */
        $announcement = $this->resource;

        /** @var AnnouncementPriority $priority */
        $priority = $announcement->priority;

        /** @var AnnouncementAudience $audience */
        $audience = $announcement->audience;

        /** @var Carbon $startsAt */
        $startsAt = $announcement->starts_at;

        /** @var Carbon|null $expiresAt */
        $expiresAt = $announcement->expires_at;

        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'body' => $announcement->body,
            'priority' => $priority->value,
            'audience' => $audience->value,
            'pinned' => $announcement->pinned,
            'startsAt' => $startsAt->toIso8601String(),
            'expiresAt' => $expiresAt?->toIso8601String(),
            'createdAt' => $announcement->created_at?->toIso8601String(),
            'updatedAt' => $announcement->updated_at?->toIso8601String(),
        ];
    }
}
