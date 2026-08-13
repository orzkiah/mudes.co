<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\StudyScheduleOccurrenceStatus;
use App\Domain\Models\StudyScheduleOccurrence;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property StudyScheduleOccurrence $resource
 */
class StudyScheduleOccurrenceResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var StudyScheduleOccurrence $occurrence */
        $occurrence = $this->resource;

        /** @var StudyScheduleOccurrenceStatus $status */
        $status = $occurrence->status;

        /** @var Carbon $occurrenceDate */
        $occurrenceDate = $occurrence->occurrence_date;

        return [
            'id' => $occurrence->id,
            'studyScheduleId' => $occurrence->study_schedule_id,
            'occurrenceDate' => $occurrenceDate->toDateString(),
            'status' => $status->value,
            'overrideNote' => $occurrence->override_note,
            'schedule' => $this->whenLoaded('schedule', fn () => new StudyScheduleResource($occurrence->schedule)),
        ];

    }
}
