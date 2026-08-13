<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Enums\ArticleStatus;
use App\Domain\Models\Article;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreArticleRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Article::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'articleCategoryId' => ['required', 'uuid', Rule::exists('article_categories', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:280', Rule::unique('articles', 'slug')->whereNull('deleted_at')],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'coverMediaId' => ['nullable', 'uuid', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'status' => ['nullable', new Enum(ArticleStatus::class)],
            'publishedAt' => ['nullable', 'date', 'required_if:status,scheduled'],
        ];
    }
}
