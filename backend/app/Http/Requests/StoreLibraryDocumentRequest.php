<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Enums\LibraryDocumentVisibility;
use App\Domain\Models\LibraryDocument;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreLibraryDocumentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LibraryDocument::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'libraryCategoryId' => ['required', 'uuid', Rule::exists('library_categories', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'fileMediaId' => [
                'nullable', 'uuid',
                Rule::exists('media', 'id')->whereNull('deleted_at'),
                'required_without:externalUrl',
                Rule::prohibitedIf(fn () => $this->filled('externalUrl')),
            ],
            'externalUrl' => [
                'nullable', 'string', 'max:500', 'url',
                'required_without:fileMediaId',
                Rule::prohibitedIf(fn () => $this->filled('fileMediaId')),
            ],
            'visibility' => ['nullable', new Enum(LibraryDocumentVisibility::class)],
        ];
    }
}
