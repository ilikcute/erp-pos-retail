<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_no')->unique();         // SES-20240801-0001
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('shift_id')->constrained('cashier_shifts');
            $table->foreignId('location_id')->nullable()->constrained('inventory_locations');
            $table->decimal('opening_cash', 18, 2)->default(0);
            $table->decimal('closing_cash', 18, 2)->nullable();
            $table->decimal('expected_cash', 18, 2)->default(0); // Dari transaksi cash
            $table->decimal('cash_difference', 18, 2)->default(0); // closing - expected
            $table->decimal('total_sales', 18, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->string('status')->default('OPEN');      // OPEN, CLOSED
            $table->text('notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['shift_id', 'status']);
            $table->index('opened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_sessions');
    }
};
