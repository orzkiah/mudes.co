<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\LibraryCategory;

class ReorderLibraryCategoryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', LibraryCategory::class) ?? false;
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
