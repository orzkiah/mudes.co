<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Shared by bulk-activate/bulk-deactivate/bulk-delete/bulk-restore
 * (API_SPECIFICATION.md §6) - identical validation shape for all four, but
 * each action requires a *different* permission (mirroring its single-item
 * equivalent exactly, per that same section), so authorization is deferred
 * to the Controller action rather than fixed here.
 */
class BulkDepartmentActionRequest extends BaseFormRequest
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
