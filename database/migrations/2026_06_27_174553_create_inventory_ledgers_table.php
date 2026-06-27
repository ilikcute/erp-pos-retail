<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // auto-generated
            $table->string('transaction_type'); // TransactionType enum
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('inventory_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('qty_change', 15, 2); // positif = masuk, negatif = keluar
            $table->decimal('qty_before', 15, 2);
            $table->decimal('qty_after', 15, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->morphs('reference'); // referensi ke transaksi (transfer/adjustment/opname)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->index(['product_variant_id', 'transaction_date']);
            $table->index(['location_id', 'transaction_date']);
            $table->index('transaction_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_ledgers');
    }
};
