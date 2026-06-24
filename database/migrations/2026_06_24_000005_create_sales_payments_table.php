<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 50)->unique();
            $table->unsignedBigInteger('sales_transaction_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('reference_no', 100)->nullable();
            $table->string('status', 30)->default('POSTED'); // PENDING|POSTED|VOID
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');

            $table->index('sales_transaction_id');
            $table->index('payment_method_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_payments');
    }
};
