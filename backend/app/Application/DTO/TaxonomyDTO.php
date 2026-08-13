<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Shared Create/Update DTO for every Taxonomy Resource Contract module
 * (API_SPECIFICATION.md §8.2) - the field shape is identical across all six
 * tables (DATABASE_SPECIFICATION.md §2.9), so one DTO class serves them all
 * instead of a near-duplicate per module.
 */
final class TaxonomyDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly ?string $icon,
        public readonly ?string $color,
        public readonly ?int $displayOrder,
        public readonly bool $isActive,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{name: string, slug?: ?string, description?: ?string, icon?: ?string, color?: ?string, displayOrder?: ?int, isActive?: bool} $validated */
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            slug: $validated['slug'] ?? null,
            description: $validated['description'] ?? null,
            icon: $validated['icon'] ?? null,
            color: $validated['color'] ?? null,
            displayOrder: $validated['displayOrder'] ?? null,
            isActive: $validated['isActive'] ?? true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug ?? Str::slug($this->name),
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'display_order' => $this->displayOrder ?? 0,
            'is_active' => $this->isActive,
        ];
    }
}
