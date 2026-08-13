<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

/**
 * DATABASE_SPECIFICATION.md §4.5. Extends Laravel's own notification model
 * rather than reimplementing it - the table shape, UUID generation, and
 * read/unread helpers it already provides are exactly what this project
 * needs (IMPLEMENTATION_RULES.md's "reuse before building").
 */
class Notification extends DatabaseNotification
{
    use HasFactory;

    protected static function newFactory(): NotificationFactory
    {
        return NotificationFactory::new();
    }
}
