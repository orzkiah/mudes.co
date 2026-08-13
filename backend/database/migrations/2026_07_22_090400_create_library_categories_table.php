<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.19 - upgraded to the full Taxonomy Table
 * Shape (§2.9); built directly in this shape (see article_categories'
 * migration note for why there is no separate upgrade step).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('library_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('color', 7)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX uq_library_categories_slug ON library_categories (slug) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_library_categories_display_order ON library_categories (display_order)');
    }

    public function down(): void
    {
        Schema::dropIfExists('library_categories');
    }
};
