<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('payment_date');
            $table->string('payment_method'); // CASH, TRANSFER, GIRO, CHEQUE
            $table->string('reference_no')->nullable();
            $table->foreignId('payment_method_account_id')->nullable()
                ->constrained('payment_methods')->nullOnDelete(); // Mapping ke COA
            $table->decimal('total_amount', 18, 2);
            $table->string('status')->default('DRAFT');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index('payment_date');
        });

        Schema::create('supplier_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_payable_id')->constrained('accounts_payables')->cascadeOnDelete();
            $table->decimal('allocated_amount', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_allocations');
        Schema::dropIfExists('supplier_payments');
    }
};
