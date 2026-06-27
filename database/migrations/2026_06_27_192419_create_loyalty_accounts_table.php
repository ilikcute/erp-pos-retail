<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->unique(); // LYL-0000001
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('current_tier_id')->nullable()->constrained('loyalty_tiers')->nullOnDelete();
            $table->integer('current_balance')->default(0); // Poin saat ini
            $table->integer('lifetime_earned')->default(0); // Total poin yang pernah didapat
            $table->integer('lifetime_redeemed')->default(0); // Total poin yang pernah ditukar
            $table->decimal('lifetime_spending', 18, 2)->default(0); // Total spending
            $table->date('point_expiry_date')->nullable(); // Tanggal kadaluarsa poin terdekat
            $table->date('tier_evaluation_date')->nullable(); // Tanggal evaluasi tier berikutnya
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('customer_id');
            $table->index('current_balance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_accounts');
    }
};
