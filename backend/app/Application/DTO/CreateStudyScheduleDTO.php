<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

final class CreateStudyScheduleDTO extends BaseDTO
{
    public function __construct(
        public readonly string $studyCategoryId,
        public readonly ?string $scheduledDate,
        public readonly int $dayOfWeek,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly ?string $topic,
        public readonly string $ustadzName,
        public readonly ?string $location,
        public readonly bool $isActive,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{studyCategoryId: string, scheduledDate?: ?string, dayOfWeek: int, startTime: string, endTime: string, topic?: ?string, ustadzName: string, location?: ?string, isActive?: bool} $validated */
        $validated = $request->validated();

        return new self(
            studyCategoryId: $validated['studyCategoryId'],
            scheduledDate: $validated['scheduledDate'] ?? null,
            dayOfWeek: (int) $validated['dayOfWeek'],
            startTime: $validated['startTime'],
            endTime: $validated['endTime'],
            topic: $validated['topic'] ?? null,
            ustadzName: $validated['ustadzName'],
            location: $validated['location'] ?? null,
            isActive: $validated['isActive'] ?? true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'study_category_id' => $this->studyCategoryId,
            'scheduled_date' => $this->scheduledDate,
            'day_of_week' => $this->dayOfWeek,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'topic' => $this->topic,
            'ustadz_name' => $this->ustadzName,
            'location' => $this->location,
            'is_active' => $this->isActive,
        ];
    }
}
