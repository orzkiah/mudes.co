<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\MemberGender;
use App\Domain\Enums\MemberStatus;
use App\Infrastructure\Observers\MemberObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.2.
 */
#[ObservedBy(MemberObserver::class)]
class Member extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'gender',
        'birth_date',
        'phone',
        'photo_media_id',
        'join_date',
        'status',
        'notes',
    ];

    protected static function newFactory(): MemberFactory
    {
        return MemberFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => MemberGender::class,
            'birth_date' => 'date',
            'join_date' => 'date',
            'status' => MemberStatus::class,
        ];
    }

    /**
     * Read-only from this module's perspective - set only via the future
     * Users module's linking flow (API_SPECIFICATION.md §9.5), which is why
     * `user_id` is deliberately absent from $fillable above.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    public function positions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrganizationPosition::class, 'member_id');
    }
}
