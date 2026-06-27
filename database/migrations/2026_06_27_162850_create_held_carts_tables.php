<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('held_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('held_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('held_cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });

        Schema::create('cart_void_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('cart_id');
            $table->string('reason');
            $table->timestamp('voided_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_void_logs');
        Schema::dropIfExists('held_cart_items');
        Schema::dropIfExists('held_carts');
    }
};
