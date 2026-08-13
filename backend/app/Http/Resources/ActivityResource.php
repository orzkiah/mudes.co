<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\ActivityStatus;
use App\Domain\Models\Activity;
use App\Domain\Models\ActivityCategory;
use App\Domain\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property Activity $resource
 */
class ActivityResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Activity $activity */
        $activity = $this->resource;

        /** @var ActivityCategory|null $category */
        $category = $activity->category;

        /** @var Media|null $cover */
        $cover = $activity->cover;

        /** @var ActivityStatus $status */
        $status = $activity->status;

        /** @var Carbon $startAt */
        $startAt = $activity->start_at;

        /** @var Carbon|null $endAt */
        $endAt = $activity->end_at;

        return [
            'id' => $activity->id,
            'activityCategoryId' => $activity->activity_category_id,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'color' => $category->color,
            ] : null,
            'title' => $activity->title,
            'slug' => $activity->slug,
            'description' => $activity->description,
            'startAt' => $startAt->toIso8601String(),
            'endAt' => $endAt?->toIso8601String(),
            'location' => $activity->location,
            'status' => $activity->getEffectiveStatus()->value,
            'cover' => $cover ? [
                'id' => $cover->id,
                'url' => $cover->getUrl(),
                'name' => $cover->name,
            ] : null,
            'createdAt' => $activity->created_at?->toIso8601String(),
            'updatedAt' => $activity->updated_at?->toIso8601String(),
        ];
    }
}
