<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\Setting;
use Illuminate\Http\Request;

/**
 * @property Setting $resource
 */
class SettingResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'key' => $this->resource->key,
            // Never round-tripped in plaintext (API_SPECIFICATION.md §9.22).
            'value' => $this->resource->is_encrypted ? '••••••••' : $this->resource->value,
            'type' => $this->resource->type,
            'group' => $this->resource->group,
            'description' => $this->resource->description,
            'isEncrypted' => $this->resource->is_encrypted,
            'autoload' => $this->resource->autoload,
            'createdAt' => $this->resource->created_at?->toIso8601String(),
            'updatedAt' => $this->resource->updated_at?->toIso8601String(),
        ];
    }
}
