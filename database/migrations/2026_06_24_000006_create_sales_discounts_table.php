<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_transaction_id');
            $table->unsignedBigInteger('sales_transaction_item_id')->nullable();
            $table->string('discount_type', 30); // MANUAL|PROMO|VOUCHER|MEMBER
            $table->decimal('discount_value', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions')->onDelete('cascade');
            $table->foreign('sales_transaction_item_id')->references('id')->on('sales_transaction_items')->onDelete('cascade');

            $table->index('sales_transaction_id');
            $table->index('discount_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_discounts');
    }
};
