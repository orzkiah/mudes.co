<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PATCH allows any subset of fields (API_SPECIFICATION.md §8.2), unlike a
 * PUT-based Update DTO - this one carries only the attributes actually
 * present in the request rather than a fixed set of typed properties, so an
 * omitted field is left untouched by the Repository's update() rather than
 * being overwritten with a default.
 */
final class UpdateDepartmentDTO extends BaseDTO
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(private readonly array $attributes)
    {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $columnNames = [
            'displayOrder' => 'display_order',
            'isActive' => 'is_active',
        ];

        $attributes = [];

        foreach ($validated as $key => $value) {
            $attributes[$columnNames[$key] ?? $key] = $value;
        }

        return new self($attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
