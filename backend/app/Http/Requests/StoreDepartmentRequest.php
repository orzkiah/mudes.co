<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\Department;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Department::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->whereNull('deleted_at')],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('departments', 'slug')->whereNull('deleted_at')],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'displayOrder' => ['nullable', 'integer', 'min:0'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
