<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.18 - photos within an album.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('gallery_photos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gallery_id');
            $table->uuid('media_id');
            $table->string('caption', 255)->nullable();
            $table->integer('display_order')->default(0);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('gallery_id', 'idx_gallery_photos_gallery');

            $table->foreign('gallery_id')->references('id')->on('galleries')->cascadeOnDelete();
            $table->foreign('media_id')->references('id')->on('media')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
    }
};
