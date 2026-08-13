<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Shared by every Taxonomy Resource Contract module's four bulk actions
 * (activate/deactivate/delete/restore) - each requires a *different*
 * permission per API_SPECIFICATION.md §6 ("mirror the single-item
 * equivalent exactly"), so per-action authorization happens in the
 * Controller instead of being fixed here (matches
 * BulkDepartmentActionRequest's precedent).
 */
class BulkTaxonomyActionRequest extends BaseFormRequest
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
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['uuid', 'distinct'],
        ];
    }
}
