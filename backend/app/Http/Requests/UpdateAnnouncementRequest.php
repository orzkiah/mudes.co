<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Enums\AnnouncementAudience;
use App\Domain\Enums\AnnouncementPriority;
use App\Domain\Models\Announcement;
use Illuminate\Validation\Rules\Enum;

class UpdateAnnouncementRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', Announcement::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
            'priority' => ['nullable', new Enum(AnnouncementPriority::class)],
            'audience' => ['nullable', new Enum(AnnouncementAudience::class)],
            'pinned' => ['sometimes', 'boolean'],
            'startsAt' => ['nullable', 'date'],
            'expiresAt' => ['nullable', 'date', 'after:startsAt'],
        ];
    }
}
