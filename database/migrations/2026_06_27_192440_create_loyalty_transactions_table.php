<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // LYT-20240801-0001
            $table->string('transaction_type'); // EARN, REDEEM, ADJUSTMENT, EXPIRE, REFUND
            $table->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();
            $table->integer('points'); // Positif = masuk, negatif = keluar
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->decimal('transaction_value', 18, 2)->nullable(); // Nilai Rupiah transaksi terkait
            $table->morphs('reference'); // Referensi ke transaksi/reward/adjustment
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->index(['loyalty_account_id', 'transaction_date']);
            $table->index('transaction_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
