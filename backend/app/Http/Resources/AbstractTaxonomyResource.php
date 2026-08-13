<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Shared shape for every Taxonomy Resource Contract module
 * (API_SPECIFICATION.md §8.2) - only the item-count field's name and value
 * differ per module (scheduleCount, activityCount, galleryCount,
 * articleCount, documentCount).
 */
abstract class AbstractTaxonomyResource extends BaseApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $model = $this->resource;

        return [
            'id' => $model->getAttribute('id'),
            'name' => $model->getAttribute('name'),
            'slug' => $model->getAttribute('slug'),
            'description' => $model->getAttribute('description'),
            'icon' => $model->getAttribute('icon'),
            'color' => $model->getAttribute('color'),
            'displayOrder' => $model->getAttribute('display_order'),
            'isActive' => $model->getAttribute('is_active'),
            $this->itemCountKey() => $this->itemCount(),
            'createdAt' => $model->getAttribute('created_at')?->toIso8601String(),
            'updatedAt' => $model->getAttribute('updated_at')?->toIso8601String(),
        ];
    }

    abstract protected function itemCountKey(): string;

    abstract protected function itemCount(): int;
}
