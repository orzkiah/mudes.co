<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Enums\StudyScheduleOccurrenceStatus;
use App\Domain\Models\StudySchedule;
use Illuminate\Validation\Rules\Enum;

class UpdateStudyScheduleOccurrenceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', StudySchedule::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', new Enum(StudyScheduleOccurrenceStatus::class)],
            'overrideNote' => ['nullable', 'string'],
        ];
    }
}
