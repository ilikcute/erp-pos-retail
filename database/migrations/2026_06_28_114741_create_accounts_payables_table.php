<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts_payables', function (Blueprint $table) {
            $table->id();
            $table->string('payable_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('invoice_id')->nullable()->constrained('supplier_invoices')->nullOnDelete();
            $table->morphs('source'); // Bisa dari invoice atau dokumen lain
            $table->date('transaction_date');
            $table->date('due_date');
            $table->decimal('total_amount', 18, 2);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->decimal('remaining_amount', 18, 2);
            $table->string('status')->default('OPEN');
            $table->string('currency', 3)->default('IDR');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_payables');
    }
};
