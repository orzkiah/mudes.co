<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\MemberGender;
use App\Domain\Enums\MemberStatus;
use App\Domain\Models\Media;
use App\Domain\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property Member $resource
 */
class MemberResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Member $member */
        $member = $this->resource;

        /** @var Media|null $photo */
        $photo = $member->photo;

        /** @var MemberGender|null $gender */
        $gender = $member->gender;

        /** @var Carbon|null $birthDate */
        $birthDate = $member->birth_date;

        /** @var Carbon|null $joinDate */
        $joinDate = $member->join_date;

        /** @var MemberStatus $status */
        $status = $member->status;

        return [
            'id' => $member->id,
            'fullName' => $member->full_name,
            'gender' => $gender?->value,
            'birthDate' => $birthDate?->toDateString(),
            'phone' => $member->phone,
            'photo' => $photo ? [
                'id' => $photo->id,
                'url' => $photo->getUrl(),
                'name' => $photo->name,
                'mimeType' => $photo->mime_type,
                'size' => $photo->size,
            ] : null,
            'joinDate' => $joinDate?->toDateString(),
            'status' => $status->value,
            'position' => $member->relationLoaded('positions') && $member->positions->isNotEmpty()
                ? [
                    'id' => $member->positions->first()->id,
                    'title' => $member->positions->first()->title,
                    'displayOrder' => $member->positions->first()->display_order,
                ]
                : null,
            'notes' => $member->notes,
            'userId' => $member->user_id,
            'createdAt' => $member->created_at?->toIso8601String(),
            'updatedAt' => $member->updated_at?->toIso8601String(),
        ];
    }
}
