<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\OrganizationPosition;
use Illuminate\Validation\Rule;

class ReorderOrganizationPositionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', OrganizationPosition::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'displayOrder' => ['required', 'integer', 'min:0'],
            'parentPositionId' => ['nullable', 'uuid', Rule::exists('organization_positions', 'id')->whereNull('deleted_at')],
            'departmentId' => ['nullable', 'uuid', Rule::exists('departments', 'id')->whereNull('deleted_at')],
        ];
    }
}
