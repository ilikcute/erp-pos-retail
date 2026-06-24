<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no', 50)->unique();
            $table->unsignedBigInteger('sales_transaction_id');
            $table->unsignedBigInteger('cashier_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('return_date');
            $table->string('status', 30)->default('POSTED'); // DRAFT|POSTED|VOID
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->text('reason');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions');
            $table->foreign('cashier_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');

            $table->index('status');
            $table->index('return_date');
            $table->index('sales_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
