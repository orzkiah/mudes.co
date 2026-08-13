<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.24.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key', 150);
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->string('group', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('autoload')->default(false);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX uq_settings_key ON settings (key) WHERE deleted_at IS NULL');
        DB::statement("ALTER TABLE settings ADD CONSTRAINT chk_settings_type CHECK (type IN ('string','number','boolean','json','encrypted'))");
        DB::statement('CREATE INDEX idx_settings_group ON settings ("group")');
        DB::statement('CREATE INDEX idx_settings_autoload ON settings (autoload) WHERE autoload = true');
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
