<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_transaction_id');
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('product_id');
            $table->string('item_name', 200);
            $table->string('sku', 100);
            $table->string('barcode', 100)->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
            $table->decimal('cost_price', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions')->onDelete('cascade');
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');

            $table->index('sales_transaction_id');
            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_transaction_items');
    }
};
