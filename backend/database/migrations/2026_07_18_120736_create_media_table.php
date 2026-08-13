<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary(); // DATABASE_SPECIFICATION.md §4.4 - UUID PK override, app-generated

            $table->string('model_type');
            $table->uuid('model_id'); // polymorphic - no FK constraint (DATABASE_SPECIFICATION.md §2.8)
            $table->index(['model_type', 'model_id']);

            $table->string('collection_name')->default('default');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();

            // Audit + soft delete extension over the package default (DATABASE_SPECIFICATION.md §2.7/§4.4).
            // Plain nullable uuid columns for now, no FK constraint: the `users` table does not exist yet
            // (it is created by the Users module, built after this infra phase). The FK is added via a
            // later migration once `users` exists.
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
