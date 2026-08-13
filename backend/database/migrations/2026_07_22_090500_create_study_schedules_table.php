<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.10 - recurring study (kajian) schedule
 * template, category-scoped.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('study_schedules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('study_category_id');
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('topic', 150)->nullable();
            $table->string('ustadz_name', 150);
            $table->string('location', 150)->nullable();
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['day_of_week', 'is_active'], 'idx_study_schedules_day_active');
            $table->index('study_category_id', 'idx_study_schedules_category');

            $table->foreign('study_category_id')->references('id')->on('study_categories')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('ALTER TABLE study_schedules ADD CONSTRAINT chk_study_schedules_day_of_week CHECK (day_of_week BETWEEN 0 AND 6)');
        DB::statement('ALTER TABLE study_schedules ADD CONSTRAINT chk_study_schedules_time_order CHECK (end_time > start_time)');
    }

    public function down(): void
    {
        Schema::dropIfExists('study_schedules');
    }
};
