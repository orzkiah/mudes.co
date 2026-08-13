<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\StudySchedule;
use Illuminate\Validation\Rule;

class UpdateStudyScheduleRequest extends BaseFormRequest
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
            'studyCategoryId' => ['required', 'uuid', Rule::exists('study_categories', 'id')->whereNull('deleted_at')],
            'scheduledDate' => ['nullable', 'date'],
            'dayOfWeek' => ['required', 'integer', 'between:0,6'],
            'startTime' => ['required', 'date_format:H:i'],
            'endTime' => ['required', 'date_format:H:i', 'after:startTime'],
            'topic' => ['nullable', 'string', 'max:150'],
            'ustadzName' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
