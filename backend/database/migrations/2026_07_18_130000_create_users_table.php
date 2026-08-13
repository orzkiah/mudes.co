<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.1. Minimal prerequisite for authentication and
 * Policy testing to function at all - not the Users business module (no
 * Repository/Service/Controller/routes here; that is still deferred).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX uq_users_email ON users (email) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_users_is_active ON users (is_active)');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
