<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared PATCH (partial update) DTO for every Taxonomy Resource Contract
 * module - holds only the attributes actually present in the request
 * (translated from camelCase to snake_case), matching
 * UpdateDepartmentDTO's array-based precedent so PATCH semantics (any
 * subset of fields) are preserved instead of forcing every field to be
 * re-sent like the Create DTO does.
 */
final class PartialTaxonomyDTO extends BaseDTO
{
    private const FIELD_MAP = [
        'name' => 'name',
        'slug' => 'slug',
        'description' => 'description',
        'icon' => 'icon',
        'color' => 'color',
        'displayOrder' => 'display_order',
        'isActive' => 'is_active',
    ];

    /**
     * @param array<string, mixed> $attributes
     */
    private function __construct(private readonly array $attributes)
    {
    }

    public static function fromRequest(FormRequest $request): static
    {
        $validated = $request->validated();
        $attributes = [];

        foreach (self::FIELD_MAP as $camel => $snake) {
            if (array_key_exists($camel, $validated)) {
                $attributes[$snake] = $validated[$camel];
            }
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
