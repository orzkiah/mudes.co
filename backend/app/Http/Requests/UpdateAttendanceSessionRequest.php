<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Enums\AttendanceSessionSourceType;
use App\Domain\Models\AttendanceSession;
use Illuminate\Validation\Rules\Enum;

class UpdateAttendanceSessionRequest extends BaseFormRequest
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
            'sourceType' => ['required', new Enum(AttendanceSessionSourceType::class)],
            'sourceId' => ['required', 'uuid'],
            'opensAt' => ['required', 'date'],
            'closesAt' => ['required', 'date', 'after:opensAt'],
        ];
    }
}
