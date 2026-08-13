<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\Department;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', Department::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $departmentId = $this->route('department');

        return [
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($departmentId)->whereNull('deleted_at')],
            'slug' => ['sometimes', 'string', 'max:120', Rule::unique('departments', 'slug')->ignore($departmentId)->whereNull('deleted_at')],
            'description' => ['sometimes', 'nullable', 'string'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:100'],
            'color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'displayOrder' => ['sometimes', 'integer', 'min:0'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
