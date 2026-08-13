<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\ActivityStatus;
use App\Infrastructure\Observers\AuditObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.13.
 */
#[ObservedBy(AuditObserver::class)]
class Activity extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'activity_category_id',
        'title',
        'slug',
        'description',
        'start_at',
        'end_at',
        'location',
        'status',
        'cover_media_id',
    ];

    protected static function newFactory(): ActivityFactory
    {
        return ActivityFactory::new();
    }

    protected static function booted(): void
    {
        static::retrieved(function (Activity $activity) {
            if ($activity->status !== ActivityStatus::Cancelled && $activity->status !== ActivityStatus::Completed) {
                $effective = $activity->getEffectiveStatus();
                if ($effective !== $activity->status) {
                    $activity->status = $effective;
                    $activity->saveQuietly();
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'status' => ActivityStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class, 'activity_category_id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function getEffectiveStatus(): ActivityStatus
    {
        if ($this->status === ActivityStatus::Cancelled) {
            return ActivityStatus::Cancelled;
        }

        $now = now();
        $start = $this->start_at;
        $end = $this->end_at ?? $start?->copy()->endOfDay();

        if ($end && $now->gt($end)) {
            return ActivityStatus::Completed;
        }

        if ($start && $now->gte($start) && ($end === null || $now->lte($end))) {
            return ActivityStatus::Ongoing;
        }

        return ActivityStatus::Upcoming;
    }
}
