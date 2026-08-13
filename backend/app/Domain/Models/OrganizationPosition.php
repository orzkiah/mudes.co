<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\OrganizationPositionType;
use App\Infrastructure\Observers\OrganizationPositionObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\OrganizationPositionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.8.
 */
#[ObservedBy(OrganizationPositionObserver::class)]
class OrganizationPosition extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'organization_period_id',
        'department_id',
        'parent_position_id',
        'member_id',
        'title',
        'position_type',
        'level',
        'display_order',
    ];

    protected static function newFactory(): OrganizationPositionFactory
    {
        return OrganizationPositionFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position_type' => OrganizationPositionType::class,
            'level' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(OrganizationPeriod::class, 'organization_period_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_position_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_position_id');
    }
}
