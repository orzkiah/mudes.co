<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\StudyScheduleOccurrenceStatus;
use App\Infrastructure\Observers\AuditObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\StudyScheduleOccurrenceFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.11.
 */
#[ObservedBy(AuditObserver::class)]
class StudyScheduleOccurrence extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'study_schedule_id',
        'occurrence_date',
        'status',
        'override_note',
    ];

    protected static function newFactory(): StudyScheduleOccurrenceFactory
    {
        return StudyScheduleOccurrenceFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurrence_date' => 'date',
            'status' => StudyScheduleOccurrenceStatus::class,
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(StudySchedule::class, 'study_schedule_id');
    }
}
