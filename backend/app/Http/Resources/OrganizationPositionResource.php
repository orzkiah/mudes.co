<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Enums\OrganizationPositionType;
use App\Domain\Models\Department;
use App\Domain\Models\Member;
use App\Domain\Models\OrganizationPosition;
use Illuminate\Http\Request;

/**
 * @property OrganizationPosition $resource
 */
class OrganizationPositionResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var OrganizationPosition $position */
        $position = $this->resource;

        /** @var Department|null $department */
        $department = $position->department;

        /** @var Member|null $member */
        $member = $position->member;

        /** @var OrganizationPositionType $positionType */
        $positionType = $position->position_type;

        $data = [
            'id' => $position->id,
            'organizationPeriodId' => $position->organization_period_id,
            'departmentId' => $position->department_id,
            'department' => $department ? [
                'id' => $department->id,
                'name' => $department->name,
                'icon' => $department->icon,
                'color' => $department->color,
            ] : null,
            'parentPositionId' => $position->parent_position_id,
            'parent' => $position->parent ? [
                'id' => $position->parent->id,
                'title' => $position->parent->title,
            ] : null,
            'positionType' => $positionType->value,
            'level' => $position->level,
            'title' => $position->title,
            'member' => $member ? [
                'id' => $member->id,
                'fullName' => $member->full_name,
                'notes' => $member->notes,
                'photo' => $member->photo ? [
                    'id' => $member->photo->id,
                    'url' => $member->photo->getUrl(),
                ] : null,
            ] : null,
            'displayOrder' => $position->display_order,
            'createdAt' => $position->created_at?->toIso8601String(),
            'updatedAt' => $position->updated_at?->toIso8601String(),
        ];

        if ($position->relationLoaded('children')) {
            $data['children'] = self::collection($position->children)->resolve();
        }

        return $data;
    }
}
