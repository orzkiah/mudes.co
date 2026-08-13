<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.20 - digital library documents; exactly one
 * of file_media_id / external_url (never both, never neither).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('library_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('library_category_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->uuid('file_media_id')->nullable();
            $table->string('external_url', 500)->nullable();
            $table->string('visibility', 20)->default('internal');
            $table->unsignedInteger('download_count')->default(0);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['visibility', 'library_category_id'], 'idx_library_documents_visibility_category');

            $table->foreign('library_category_id')->references('id')->on('library_categories')->restrictOnDelete();
            $table->foreign('file_media_id')->references('id')->on('media')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('ALTER TABLE library_documents ADD CONSTRAINT chk_library_documents_source_required CHECK (file_media_id IS NOT NULL OR external_url IS NOT NULL)');
        DB::statement('ALTER TABLE library_documents ADD CONSTRAINT chk_library_documents_source_exclusive CHECK (NOT (file_media_id IS NOT NULL AND external_url IS NOT NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('library_documents');
    }
};
