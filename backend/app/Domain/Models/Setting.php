<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Infrastructure\Observers\SettingObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.24.
 */
#[ObservedBy(SettingObserver::class)]
class Setting extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_encrypted',
    ];

    protected static function newFactory(): SettingFactory
    {
        return SettingFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
            'autoload' => 'boolean',
        ];
    }
}
