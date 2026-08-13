<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.15 - blog-style content; author is the
 * existing created_by audit column (§10.2 decision), no separate author_id.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('article_category_id');
            $table->string('title', 255);
            $table->string('slug', 280);
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->uuid('cover_media_id')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at'], 'idx_articles_status_published');
            $table->index('article_category_id', 'idx_articles_category');

            $table->foreign('article_category_id')->references('id')->on('article_categories')->restrictOnDelete();
            $table->foreign('cover_media_id')->references('id')->on('media')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX uq_articles_slug ON articles (slug) WHERE deleted_at IS NULL');
        DB::statement("ALTER TABLE articles ADD CONSTRAINT chk_articles_published_at CHECK (status NOT IN ('scheduled','published') OR published_at IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
