<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\StudyCategory;
use App\Domain\Models\StudySchedule;
use Illuminate\Http\Request;

/**
 * @property StudySchedule $resource
 */
class StudyScheduleResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var StudySchedule $schedule */
        $schedule = $this->resource;

        /** @var StudyCategory|null $category */
        $category = $schedule->category;

        return [
            'id' => $schedule->id,
            'studyCategoryId' => $schedule->study_category_id,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'color' => $category->color,
            ] : null,
            'dayOfWeek' => $schedule->day_of_week,
            'scheduledDate' => $schedule->scheduled_date?->toDateString(),
            'startTime' => $schedule->start_time,
            'endTime' => $schedule->end_time,
            'topic' => $schedule->topic,
            'ustadzName' => $schedule->ustadz_name,
            'location' => $schedule->location,
            'isActive' => $schedule->is_active,
            'createdAt' => $schedule->created_at?->toIso8601String(),
            'updatedAt' => $schedule->updated_at?->toIso8601String(),
        ];
    }
}
