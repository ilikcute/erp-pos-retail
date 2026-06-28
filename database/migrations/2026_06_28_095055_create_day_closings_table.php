<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_closings', function (Blueprint $table) {
            $table->id();
            $table->date('closing_date')->unique();
            $table->string('closing_number')->unique();     // DC-20240801-0001
            $table->foreignId('location_id')->nullable()->constrained('inventory_locations');

            // Ringkasan transaksi
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 18, 2)->default(0);
            $table->decimal('total_cash', 18, 2)->default(0);
            $table->decimal('total_non_cash', 18, 2)->default(0);
            $table->decimal('total_discount', 18, 2)->default(0);
            $table->decimal('total_tax', 18, 2)->default(0);

            // Cash reconciliation
            $table->decimal('total_opening_cash', 18, 2)->default(0);
            $table->decimal('total_closing_cash', 18, 2)->default(0);
            $table->decimal('total_expected_cash', 18, 2)->default(0);
            $table->decimal('cash_difference', 18, 2)->default(0);

            // Status & audit
            $table->string('status')->default('OPEN');       // OPEN, CLOSED
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['closing_date', 'status']);
            $table->index('location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_closings');
    }
};
