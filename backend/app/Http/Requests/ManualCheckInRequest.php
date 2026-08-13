<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\AttendanceSession;
use Illuminate\Validation\Rule;

class ManualCheckInRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', AttendanceSession::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'memberId' => ['nullable', 'uuid', Rule::exists('members', 'id')->whereNull('deleted_at'), 'required_without:memberName'],
            'memberName' => ['nullable', 'string', 'max:150', 'required_without:memberId'],
        ];
    }
}
