<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_reward_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('reward_code')->unique();
            $table->string('reward_name');
            $table->string('reward_type'); // VOUCHER, PRODUCT, LUCKY_DRAW
            $table->integer('point_required');
            $table->decimal('voucher_amount', 18, 2)->nullable(); // Untuk VOUCHER
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Untuk VOUCHER %
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); // Untuk PRODUCT
            $table->integer('stock_qty')->default(0);
            $table->integer('redeemed_qty')->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['reward_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_reward_catalogs');
    }
};
