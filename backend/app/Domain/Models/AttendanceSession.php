<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\AttendanceSessionSourceType;
use App\Infrastructure\Observers\AuditObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\AttendanceSessionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.22.
 */
#[ObservedBy(AuditObserver::class)]
class AttendanceSession extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'source_type',
        'source_id',
        'qr_token',
        'opens_at',
        'closes_at',
    ];

    protected static function newFactory(): AttendanceSessionFactory
    {
        return AttendanceSessionFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source_type' => AttendanceSessionSourceType::class,
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isOpenAt(\DateTimeInterface $moment): bool
    {
        /** @var \Illuminate\Support\Carbon $opensAt */
        $opensAt = $this->opens_at;

        /** @var \Illuminate\Support\Carbon $closesAt */
        $closesAt = $this->closes_at;

        return $moment >= $opensAt && $moment <= $closesAt;
    }
}
