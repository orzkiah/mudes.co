<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.13 - one-time/periodic organizational
 * activities, informational only (no registration).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('activity_category_id');
            $table->string('title', 150);
            $table->string('slug', 180);
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('location', 150)->nullable();
            $table->string('status', 20)->default('upcoming');
            $table->uuid('cover_media_id')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'start_at'], 'idx_activities_status_start');
            $table->index('activity_category_id', 'idx_activities_category');

            $table->foreign('activity_category_id')->references('id')->on('activity_categories')->restrictOnDelete();
            $table->foreign('cover_media_id')->references('id')->on('media')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX uq_activities_slug ON activities (slug) WHERE deleted_at IS NULL');
        DB::statement('ALTER TABLE activities ADD CONSTRAINT chk_activities_end_after_start CHECK (end_at IS NULL OR end_at > start_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
