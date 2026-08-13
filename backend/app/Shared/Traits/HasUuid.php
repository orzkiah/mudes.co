<?php

declare(strict_types=1);

namespace App\Shared\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * UUIDv7 primary keys, generated at the application layer
 * (DATABASE_SPECIFICATION.md §2.1). UUIDv7 is time-ordered, avoiding the
 * B-tree index fragmentation that random UUIDv4 keys cause at scale - this
 * matters because every table in this schema uses a UUID PK.
 *
 * @mixin Model
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function (Model $model): void {
            $keyName = $model->getKeyName();

            if (empty($model->{$keyName})) {
                $model->{$keyName} = (string) Str::uuid7();
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }
}
