<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Concrete Resources define their own explicit, allow-listed toArray()
 * (IMPLEMENTATION_RULES.md §9). The outer response envelope
 * (success/message/data/meta/errors) is applied by
 * App\Shared\Support\ApiResponse, never by the Resource itself.
 */
abstract class BaseApiResource extends JsonResource
{
}
