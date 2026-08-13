<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\StudyCategory;
use Illuminate\Validation\Rule;

class UpdateStudyCategoryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', StudyCategory::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'slug' => ['sometimes', 'string', 'max:120', Rule::unique('study_categories', 'slug')->ignore($this->route('studyCategory'))->whereNull('deleted_at')],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'displayOrder' => ['nullable', 'integer', 'min:0'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
