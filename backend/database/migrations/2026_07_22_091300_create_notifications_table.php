<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.5 - backing store for Laravel's native
 * Notification system. Deliberately excluded from the audit/soft-delete
 * baseline (§2.2/§2.3) - system-generated and expected to be pruned, not a
 * durable business record.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id'], 'idx_notifications_notifiable');
        });

        DB::statement('CREATE INDEX idx_notifications_unread ON notifications (notifiable_type, notifiable_id) WHERE read_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
