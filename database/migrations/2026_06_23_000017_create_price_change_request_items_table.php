<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_change_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_change_request_id');
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('unit_id');
            $table->decimal('old_price', 18, 2);
            $table->decimal('new_price', 18, 2);
            $table->string('change_reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('price_change_request_id', 'pcri_request_fk')
                ->references('id')->on('price_change_requests')->onDelete('cascade');
            $table->foreign('product_variant_id', 'pcri_variant_fk')
                ->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('unit_id', 'pcri_unit_fk')
                ->references('id')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_change_request_items');
    }
};
