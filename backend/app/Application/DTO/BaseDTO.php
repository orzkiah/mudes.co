<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Immutable data crossing the Controller -> Service boundary
 * (IMPLEMENTATION_RULES.md §7). Concrete DTOs are readonly classes built via
 * fromRequest(); this base class only fixes the contract every DTO must
 * satisfy so Services never depend on Illuminate\Http\Request directly.
 *
 * @implements Arrayable<string, mixed>
 */
abstract class BaseDTO implements Arrayable
{
    abstract public static function fromRequest(FormRequest $request): static;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
