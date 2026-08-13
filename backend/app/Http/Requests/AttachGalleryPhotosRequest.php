<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\Gallery;
use Illuminate\Validation\Rule;

class AttachGalleryPhotosRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', Gallery::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mediaIds' => ['required', 'array', 'min:1', 'max:50'],
            'mediaIds.*' => ['uuid', 'distinct', Rule::exists('media', 'id')->whereNull('deleted_at')],
        ];
    }
}
