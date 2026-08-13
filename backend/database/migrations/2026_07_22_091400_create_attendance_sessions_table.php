<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.22 - polymorphic source (study schedule
 * occurrence or activity), one session per source, time-bound QR token.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('source_type', 30);
            $table->uuid('source_id');
            $table->string('qr_token', 100);
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['source_type', 'source_id'], 'idx_attendance_sessions_source');

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX uq_attendance_sessions_qr_token ON attendance_sessions (qr_token) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX uq_attendance_sessions_source ON attendance_sessions (source_type, source_id) WHERE deleted_at IS NULL');
        DB::statement('ALTER TABLE attendance_sessions ADD CONSTRAINT chk_attendance_sessions_window CHECK (closes_at > opens_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
