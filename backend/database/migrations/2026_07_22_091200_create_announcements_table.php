<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.21 - time-sensitive broadcasts.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title', 200);
            $table->text('body');
            $table->string('priority', 20)->default('normal');
            $table->string('audience', 20)->default('public');
            $table->boolean('pinned')->default(false);
            $table->dateTime('starts_at');
            $table->dateTime('expires_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['audience', 'expires_at'], 'idx_announcements_audience_expires');
            $table->index('pinned', 'idx_announcements_pinned');

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('ALTER TABLE announcements ADD CONSTRAINT chk_announcements_expiry_after_start CHECK (expires_at IS NULL OR expires_at > starts_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
