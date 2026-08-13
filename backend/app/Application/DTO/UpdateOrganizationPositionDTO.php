<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateOrganizationPositionDTO extends BaseDTO
{
    public function __construct(
        public readonly string $organizationPeriodId,
        public readonly ?string $departmentId,
        public readonly ?string $parentPositionId,
        public readonly ?string $memberId,
        public readonly string $title,
        public readonly string $positionType,
        public readonly int $displayOrder,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{organizationPeriodId: string, departmentId?: ?string, parentPositionId?: ?string, memberId?: ?string, title: string, positionType: string, displayOrder?: ?int} $validated */
        $validated = $request->validated();

        return new self(
            organizationPeriodId: $validated['organizationPeriodId'],
            departmentId: $validated['departmentId'] ?? null,
            parentPositionId: $validated['parentPositionId'] ?? null,
            memberId: $validated['memberId'] ?? null,
            title: $validated['title'],
            positionType: $validated['positionType'],
            displayOrder: $validated['displayOrder'] ?? 0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'organization_period_id' => $this->organizationPeriodId,
            'department_id' => $this->departmentId,
            'parent_position_id' => $this->parentPositionId,
            'member_id' => $this->memberId,
            'title' => $this->title,
            'position_type' => $this->positionType,
            'display_order' => $this->displayOrder,
        ];
    }
}
