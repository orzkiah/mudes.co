<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\StudyCategory;

class ReorderStudyCategoryRequest extends BaseFormRequest
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
            'order' => ['required', 'array', 'min:1', 'max:100'],
            'order.*' => ['uuid', 'distinct'],
        ];
    }
}
