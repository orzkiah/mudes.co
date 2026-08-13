<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\User;
use Illuminate\Http\Request;

/**
 * @property User $resource
 */
class AuthUserResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'roles' => $this->resource->getRoleNames()->values(),
            'permissions' => $this->resource->getAllPermissions()->pluck('name')->values(),
        ];
    }
}
