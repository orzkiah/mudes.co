<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAnnouncementDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $priority,
        public readonly string $audience,
        public readonly bool $pinned,
        public readonly string $startsAt,
        public readonly ?string $expiresAt,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{title: string, body: string, priority?: ?string, audience?: ?string, pinned?: bool, startsAt?: ?string, expiresAt?: ?string} $validated */
        $validated = $request->validated();

        return new self(
            title: $validated['title'],
            body: $validated['body'],
            priority: $validated['priority'] ?? 'normal',
            audience: $validated['audience'] ?? 'public',
            pinned: $validated['pinned'] ?? false,
            startsAt: $validated['startsAt'] ?? now()->toIso8601String(),
            expiresAt: $validated['expiresAt'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'priority' => $this->priority,
            'audience' => $this->audience,
            'pinned' => $this->pinned,
            'starts_at' => $this->startsAt,
            'expires_at' => $this->expiresAt,
        ];
    }
}
