<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_voids', function (Blueprint $table) {
            $table->id();
            $table->string('void_no', 50)->unique();
            $table->unsignedBigInteger('sales_transaction_id');
            $table->string('status', 30)->default('APPROVED'); // PENDING|APPROVED|REJECTED
            $table->text('reason');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions');
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users')->nullable();

            $table->index('sales_transaction_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_voids');
    }
};
