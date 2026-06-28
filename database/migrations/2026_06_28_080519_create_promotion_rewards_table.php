<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->string('reward_type'); // PERCENTAGE, FIXED_AMOUNT, FREE_PRODUCT, SPECIAL_PRICE
            $table->decimal('reward_value', 18, 2); // 17 for 17%, or 50000 for Rp 50.000
            $table->decimal('max_discount', 18, 2)->nullable(); // Cap for percentage
            $table->foreignId('free_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('free_product_qty')->default(1);
            $table->timestamps();

            $table->index('promotion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_rewards');
    }
};
