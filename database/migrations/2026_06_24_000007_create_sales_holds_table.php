<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_holds', function (Blueprint $table) {
            $table->id();
            $table->string('hold_no', 50)->unique();
            $table->unsignedBigInteger('sales_session_id');
            $table->unsignedBigInteger('cashier_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('status', 30)->default('HELD'); // HELD|RESUMED|CANCELLED
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('held_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamps();

            $table->foreign('sales_session_id')->references('id')->on('sales_sessions');
            $table->foreign('cashier_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');

            $table->index('status');
            $table->index(['sales_session_id', 'status']);
            $table->index('cashier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_holds');
    }
};
