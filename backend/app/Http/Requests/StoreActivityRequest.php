<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Enums\ActivityStatus;
use App\Domain\Models\Activity;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreActivityRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Activity::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'activityCategoryId' => ['required', 'uuid', Rule::exists('activity_categories', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'startAt' => ['required', 'date'],
            'endAt' => ['nullable', 'date', 'after:startAt'],
            'location' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', new Enum(ActivityStatus::class)],
            'coverMediaId' => ['nullable', 'uuid', Rule::exists('media', 'id')->whereNull('deleted_at')],
        ];
    }
}
