<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UploadMediaRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'collection' => [
                'required',
                'string',
                Rule::in([
                    'member-photo',
                    'article-cover',
                    'activity-cover',
                    'gallery-photo',
                    'library-file',
                ]),
            ],
            'file' => [
                'required',
                'max:102400', // Maksimal 100 MB
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'collection.required' => 'Collection wajib diisi.',
            'collection.in' => 'Collection tidak valid. Pilih salah satu: member-photo, article-cover, activity-cover, gallery-photo, library-file.',
            'file.required' => 'File wajib diunggah.',
            'file.max' => 'Ukuran file melebihi batas maksimum 100 MB.',
        ];
    }
}
