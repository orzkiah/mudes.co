<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * DATABASE_SPECIFICATION.md §4.4 - UUID PK override, app-generated. Spatie's
 * base Media model assumes its own default auto-incrementing bigint `id`
 * (it never overrides $incrementing/$keyType), so it cannot correctly
 * persist a UUID primary key on its own - this thin subclass adds that
 * override and is registered as the package's model via
 * config/media-library.php's `media_model`, which exists exactly for this
 * kind of customization.
 *
 * Deliberately does NOT use this project's own Shared\Traits\HasUuid trait:
 * Spatie's own Concerns\HasUuid trait (already applied by the parent class,
 * for its separate `uuid` tracking column) shares the same base class name,
 * and Eloquent's boot{Trait} convention would collide the two bootHasUuid()
 * methods with incompatible visibility. The same id-generation logic is
 * inlined here instead.
 */
class Media extends SpatieMedia
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid7();
            }
        });
    }
}
