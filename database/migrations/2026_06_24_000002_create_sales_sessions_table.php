<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_no', 50)->unique();
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('cashier_id');
            $table->date('session_date');
            $table->string('status', 30)->default('OPEN'); // OPEN|CLOSED
            $table->decimal('opening_cash', 18, 2)->default(0);
            $table->decimal('closing_cash', 18, 2)->nullable();
            $table->decimal('expected_cash', 18, 2)->nullable();
            $table->decimal('cash_difference', 18, 2)->nullable();
            $table->decimal('total_sales', 18, 2)->default(0);
            $table->decimal('total_transactions', 18, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('shifts');
            $table->foreign('cashier_id')->references('id')->on('users');

            $table->index('status');
            $table->index(['cashier_id', 'status']);
            $table->index('session_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_sessions');
    }
};
