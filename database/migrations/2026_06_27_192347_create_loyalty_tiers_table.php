<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('tier_code')->unique(); // BRONZE, SILVER, GOLD, PLATINUM
            $table->string('tier_name');
            $table->decimal('minimum_spending', 18, 2)->default(0); // Total spending untuk naik tier
            $table->integer('minimum_points')->default(0);
            $table->decimal('point_multiplier', 5, 2)->default(1.0); // 1.5 = 1.5x poin per transaksi
            $table->decimal('discount_percentage', 5, 2)->default(0); // Diskon tambahan
            $table->text('benefits')->nullable();
            $table->integer('sort_order')->default(0); // Untuk urutan tier
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_tiers');
    }
};
