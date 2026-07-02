<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_supplier_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('supplier_sku', 100)->nullable();
            $table->string('supplier_product_name', 200)->nullable();
            $table->decimal('min_order_qty', 18, 4)->default(0);
            $table->boolean('is_preferred')->default(false);
            $table->integer('lead_time_days')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->unique(['product_id', 'supplier_id'], 'uk_product_supplier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_supplier_links');
    }
};
