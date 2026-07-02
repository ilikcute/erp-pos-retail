<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('unit_id');
            $table->decimal('old_price', 18, 2);
            $table->decimal('new_price', 18, 2);
            $table->unsignedBigInteger('changed_by');
            $table->string('change_source', 50)->default('MANUAL'); // MANUAL|PRICE_CHANGE_REQUEST|IMPORT
            $table->unsignedBigInteger('price_change_request_id')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('changed_at');

            $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('cascade');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('changed_by')->references('id')->on('users');
            $table->foreign('price_change_request_id')->references('id')->on('price_change_requests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
