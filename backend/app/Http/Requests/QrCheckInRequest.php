<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Public, token-scoped check-in (PROJECT_SPECIFICATION.md §15 - "requires
 * no login") - authorize() is intentionally always true, the qr_token
 * itself (validated in AttendanceSessionService::checkInByToken()) is the
 * access control, not a permission.
 */
class QrCheckInRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'qrToken' => ['required', 'string'],
            'memberId' => ['required', 'uuid', Rule::exists('members', 'id')->whereNull('deleted_at')],
        ];
    }
}
