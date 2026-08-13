<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('study_schedules', function (Blueprint $table): void {
            $table->date('scheduled_date')->nullable()->after('study_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('study_schedules', function (Blueprint $table): void {
            $table->dropColumn('scheduled_date');
        });
    }
};
