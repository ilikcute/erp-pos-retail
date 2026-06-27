<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_redemptions', function (Blueprint $table) {
            $table->id();
            $table->string('redemption_number')->unique(); // LRD-20240801-0001
            $table->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_catalog_id')->constrained('loyalty_reward_catalogs')->cascadeOnDelete();
            $table->integer('points_used');
            $table->decimal('reward_value', 18, 2)->nullable(); // Nilai Rupiah reward
            $table->string('status')->default('PENDING');
            $table->string('voucher_code')->nullable(); // Generated voucher code
            $table->date('voucher_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemptions');
    }
};
