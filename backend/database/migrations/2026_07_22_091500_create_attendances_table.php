<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATABASE_SPECIFICATION.md §4.23 - individual check-ins; member_name is a
 * denormalized snapshot preserved even if the member is later removed.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('attendance_session_id');
            $table->uuid('member_id')->nullable();
            $table->string('member_name', 150);
            $table->string('method', 20);
            $table->dateTime('checked_in_at');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('attendance_session_id', 'idx_attendances_session');
            $table->index('member_id', 'idx_attendances_member');

            $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->cascadeOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX uq_attendances_session_member ON attendances (attendance_session_id, member_id) WHERE deleted_at IS NULL AND member_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
