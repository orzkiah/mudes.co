<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.8 - department-scoped, type-classified,
 * depth-tracked org chart positions with unlimited hierarchy depth.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('organization_positions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_period_id');
            $table->uuid('department_id')->nullable();
            $table->uuid('parent_position_id')->nullable();
            $table->uuid('member_id')->nullable();
            $table->string('title', 150);
            $table->string('position_type', 20)->default('member');
            $table->smallInteger('level')->default(0);
            $table->integer('display_order')->default(0);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_period_id', 'idx_organization_positions_period');
            $table->index('parent_position_id', 'idx_organization_positions_parent');
            $table->index('department_id', 'idx_organization_positions_department');
            $table->index('position_type', 'idx_organization_positions_type');
            $table->index(['department_id', 'level'], 'idx_organization_positions_department_level');

            $table->foreign('organization_period_id')->references('id')->on('organization_periods')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        // Self-referencing FK added in a follow-up Schema::table() call - Postgres
        // won't resolve a table's own not-yet-committed primary key when the
        // constraint is declared inside the same Schema::create() blueprint.
        Schema::table('organization_positions', function (Blueprint $table): void {
            $table->foreign('parent_position_id')->references('id')->on('organization_positions')->nullOnDelete();
        });

        DB::statement("ALTER TABLE organization_positions ADD CONSTRAINT chk_organization_positions_type CHECK (position_type IN ('chairman','vice_chairman','secretary','treasurer','coordinator','member'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_positions');
    }
};
