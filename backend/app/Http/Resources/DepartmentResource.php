<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @property \App\Domain\Models\Department $resource
 */
class DepartmentResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'icon' => $this->resource->icon,
            'color' => $this->resource->color,
            'displayOrder' => $this->resource->display_order,
            'isActive' => $this->resource->is_active,
            'positionCount' => $this->resource->positions()->count(),
            'createdAt' => $this->resource->created_at?->toIso8601String(),
            'updatedAt' => $this->resource->updated_at?->toIso8601String(),
        ];
    }
}
