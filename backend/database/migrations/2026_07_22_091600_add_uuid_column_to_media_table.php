<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spatie Media Library's own Concerns\HasUuid trait auto-generates a
 * `uuid` attribute on every Media row (distinct from this project's `id`
 * UUID primary key, used internally by the package e.g. for temporary
 * uploads) - the original create_media_table migration didn't anticipate
 * this package-internal column, only surfacing once a real upload/create
 * path was exercised.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
