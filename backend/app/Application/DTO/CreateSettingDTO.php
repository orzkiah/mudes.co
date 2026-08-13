<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

final class CreateSettingDTO extends BaseDTO
{
    public function __construct(
        public readonly string $key,
        public readonly ?string $value,
        public readonly string $type,
        public readonly ?string $group,
        public readonly ?string $description,
        public readonly bool $isEncrypted,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{key: string, value?: ?string, type: string, group?: ?string, description?: ?string, isEncrypted?: bool} $validated */
        $validated = $request->validated();

        return new self(
            key: $validated['key'],
            value: $validated['value'] ?? null,
            type: $validated['type'],
            group: $validated['group'] ?? null,
            description: $validated['description'] ?? null,
            isEncrypted: $validated['isEncrypted'] ?? false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'group' => $this->group,
            'description' => $this->description,
            'is_encrypted' => $this->isEncrypted,
        ];
    }
}
