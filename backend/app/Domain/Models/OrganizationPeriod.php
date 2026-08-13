<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Shared\Traits\HasUuid;
use Database\Factories\OrganizationPeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.7 - minimal prerequisite for
 * `organization_positions`; full CRUD module deferred (see migration note).
 */
class OrganizationPeriod extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'label',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected static function newFactory(): OrganizationPeriodFactory
    {
        return OrganizationPeriodFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(OrganizationPosition::class);
    }
}
