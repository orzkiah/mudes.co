<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\ArticleCategory;
use Illuminate\Validation\Rule;

class StoreArticleCategoryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ArticleCategory::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('article_categories', 'slug')->whereNull('deleted_at')],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'displayOrder' => ['nullable', 'integer', 'min:0'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
