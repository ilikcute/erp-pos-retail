<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('month_closings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('closing_year');
            $table->unsignedTinyInteger('closing_month');
            $table->foreignId('location_id')->nullable()->constrained('inventory_locations');

            // Ringkasan bulanan
            $table->integer('total_days_closed')->default(0);
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 18, 2)->default(0);
            $table->decimal('total_cash', 18, 2)->default(0);
            $table->decimal('total_non_cash', 18, 2)->default(0);

            // Status & audit
            $table->string('status')->default('OPEN');       // OPEN, CLOSED, LOCKED
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['closing_year', 'closing_month', 'location_id'], 'uniq_month_location');
            $table->index(['closing_year', 'closing_month', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('month_closings');
    }
};
