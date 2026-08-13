<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Infrastructure\Observers\AuditObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\StudyScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.10.
 */
#[ObservedBy(AuditObserver::class)]
class StudySchedule extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'study_category_id',
        'scheduled_date',
        'day_of_week',
        'start_time',
        'end_time',
        'topic',
        'ustadz_name',
        'location',
        'is_active',
    ];

    protected static function newFactory(): StudyScheduleFactory
    {
        return StudyScheduleFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(StudyCategory::class, 'study_category_id');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(StudyScheduleOccurrence::class);
    }
}
