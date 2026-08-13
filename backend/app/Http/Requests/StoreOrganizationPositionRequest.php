<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Enums\OrganizationPositionType;
use App\Domain\Models\OrganizationPosition;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreOrganizationPositionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', OrganizationPosition::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organizationPeriodId' => ['required', 'uuid', Rule::exists('organization_periods', 'id')->whereNull('deleted_at')],
            'departmentId' => ['nullable', 'uuid', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'parentPositionId' => ['nullable', 'uuid', Rule::exists('organization_positions', 'id')->whereNull('deleted_at')],
            'memberId' => ['nullable', 'uuid', Rule::exists('members', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:150'],
            'positionType' => ['required', new Enum(OrganizationPositionType::class)],
            'displayOrder' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
