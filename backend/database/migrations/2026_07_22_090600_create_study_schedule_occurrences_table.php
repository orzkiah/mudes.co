<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.11 - dated occurrences generated from a
 * study_schedules template; each can be individually rescheduled/cancelled
 * without altering the template (PROJECT_SPECIFICATION.md §3.7).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('study_schedule_occurrences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('study_schedule_id');
            $table->date('occurrence_date');
            $table->string('status', 20)->default('scheduled');
            $table->text('override_note')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('study_schedule_id', 'idx_study_schedule_occurrences_schedule');
            $table->index('occurrence_date', 'idx_study_schedule_occurrences_date');

            $table->foreign('study_schedule_id')->references('id')->on('study_schedules')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_schedule_occurrences');
    }
};
