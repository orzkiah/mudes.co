<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.7 - unchanged from v1.0.0. Minimal
 * prerequisite for `organization_positions.organization_period_id` (a
 * required FK) - the full Organization Periods CRUD module (its own
 * Repository/Service/Controller/routes) is out of scope for this step and
 * deferred, matching the precedent set by the minimal `users` table built
 * ahead of the Users module.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('organization_periods', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('label', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX uq_organization_periods_active ON organization_periods (is_active) WHERE is_active = true AND deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_periods');
    }
};
