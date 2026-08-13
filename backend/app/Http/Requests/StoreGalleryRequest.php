<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\Gallery;
use Illuminate\Validation\Rule;

class StoreGalleryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Gallery::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'galleryCategoryId' => ['required', 'uuid', Rule::exists('gallery_categories', 'id')->whereNull('deleted_at')],
            'activityId' => ['nullable', 'uuid', Rule::exists('activities', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'coverPhotoMediaId' => ['nullable', 'uuid', Rule::exists('media', 'id')->whereNull('deleted_at')],
        ];
    }
}
