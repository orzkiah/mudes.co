<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\AnnouncementAudience;
use App\Domain\Enums\AnnouncementPriority;
use App\Infrastructure\Observers\AuditObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.21.
 */
#[ObservedBy(AuditObserver::class)]
class Announcement extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'priority',
        'audience',
        'pinned',
        'starts_at',
        'expires_at',
    ];

    protected static function newFactory(): AnnouncementFactory
    {
        return AnnouncementFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => AnnouncementPriority::class,
            'audience' => AnnouncementAudience::class,
            'pinned' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
