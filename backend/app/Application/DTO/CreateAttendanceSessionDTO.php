<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

final class CreateAttendanceSessionDTO extends BaseDTO
{
    public function __construct(
        public readonly string $sourceType,
        public readonly string $sourceId,
        public readonly string $opensAt,
        public readonly string $closesAt,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{sourceType: string, sourceId: string, opensAt: string, closesAt: string} $validated */
        $validated = $request->validated();

        return new self(
            sourceType: $validated['sourceType'],
            sourceId: $validated['sourceId'],
            opensAt: $validated['opensAt'],
            closesAt: $validated['closesAt'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'opens_at' => $this->opensAt,
            'closes_at' => $this->closesAt,
        ];
    }
}
