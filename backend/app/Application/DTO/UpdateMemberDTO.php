<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMemberDTO extends BaseDTO
{
    public function __construct(
        public readonly string $fullName,
        public readonly ?string $gender,
        public readonly ?string $birthDate,
        public readonly ?string $phone,
        public readonly ?string $photoMediaId,
        public readonly ?string $joinDate,
        public readonly string $status,
        public readonly ?string $notes,
    ) {
    }

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array{fullName: string, gender?: ?string, birthDate?: ?string, phone?: ?string, photoMediaId?: ?string, joinDate?: ?string, status?: ?string, notes?: ?string} $validated */
        $validated = $request->validated();

        return new self(
            fullName: $validated['fullName'],
            gender: $validated['gender'] ?? null,
            birthDate: $validated['birthDate'] ?? null,
            phone: $validated['phone'] ?? null,
            photoMediaId: $validated['photoMediaId'] ?? null,
            joinDate: $validated['joinDate'] ?? null,
            status: $validated['status'] ?? 'active',
            notes: $validated['notes'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'gender' => $this->gender,
            'birth_date' => $this->birthDate,
            'phone' => $this->phone,
            'photo_media_id' => $this->photoMediaId,
            'join_date' => $this->joinDate,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
