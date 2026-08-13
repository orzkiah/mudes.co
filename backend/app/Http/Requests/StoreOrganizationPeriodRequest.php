<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\OrganizationPeriod;

class StoreOrganizationPeriodRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', OrganizationPeriod::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'isActive' => ['boolean'],
        ];
    }
}
