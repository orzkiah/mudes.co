<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Enums\MemberGender;
use App\Domain\Enums\MemberStatus;
use App\Domain\Models\Member;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreMemberRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Member::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', new Enum(MemberGender::class)],
            'birthDate' => ['nullable', 'date', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'photoMediaId' => [
                'nullable',
                'uuid',
                Rule::exists('media', 'id')->where(function (Builder $query): void {
                    $query->where('collection_name', 'member-photo')->whereNull('deleted_at');
                }),
            ],
            'joinDate' => ['nullable', 'date'],
            'status' => ['nullable', new Enum(MemberStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
