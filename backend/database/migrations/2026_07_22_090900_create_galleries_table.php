<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.17 - photo albums, primarily organized by
 * gallery_category_id; activity_id remains a secondary, optional
 * cross-reference.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gallery_category_id');
            $table->uuid('activity_id')->nullable();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->uuid('cover_photo_media_id')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('activity_id', 'idx_galleries_activity');
            $table->index('gallery_category_id', 'idx_galleries_category');

            $table->foreign('gallery_category_id')->references('id')->on('gallery_categories')->restrictOnDelete();
            $table->foreign('activity_id')->references('id')->on('activities')->nullOnDelete();
            $table->foreign('cover_photo_media_id')->references('id')->on('media')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
