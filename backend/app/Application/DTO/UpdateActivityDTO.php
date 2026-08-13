<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class UpdateActivityDTO extends BaseDTO
{
    public function __construct(
        public readonly string $activityCategoryId,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly string $startAt,
        public readonly ?string $endAt,
        public readonly ?string $location,
        public readonly string $status,
        public readonly ?string $coverMediaId,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{activityCategoryId: string, title: string, slug?: ?string, description?: ?string, startAt: string, endAt?: ?string, location?: ?string, status?: ?string, coverMediaId?: ?string} $validated */
        $validated = $request->validated();

        return new self(
            activityCategoryId: $validated['activityCategoryId'],
            title: $validated['title'],
            slug: $validated['slug'] ?? null,
            description: $validated['description'] ?? null,
            startAt: $validated['startAt'],
            endAt: $validated['endAt'] ?? null,
            location: $validated['location'] ?? null,
            status: $validated['status'] ?? 'upcoming',
            coverMediaId: $validated['coverMediaId'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'activity_category_id' => $this->activityCategoryId,
            'title' => $this->title,
            'slug' => $this->generateUniqueSlug($this->title, $this->slug, $this->getRequestActivityId()),
            'description' => $this->description,
            'start_at' => $this->startAt,
            'end_at' => $this->endAt,
            'location' => $this->location,
            'status' => $this->status,
            'cover_media_id' => $this->coverMediaId,
        ];
    }

    private function getRequestActivityId(): ?string
    {
        /** @var Activity|string|null $activity */
        $activity = request()->route('activity');
        if ($activity instanceof Activity) {
            return $activity->id;
        }

        return is_string($activity) ? $activity : null;
    }

    private function generateUniqueSlug(string $title, ?string $customSlug = null, ?string $ignoreId = null): string
    {
        $baseSlug = $customSlug && trim($customSlug) !== '' ? Str::slug($customSlug) : Str::slug($title);
        if ($baseSlug === '') {
            $baseSlug = 'kegiatan';
        }

        $slug = $baseSlug;
        $count = 1;

        while (
            Activity::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
