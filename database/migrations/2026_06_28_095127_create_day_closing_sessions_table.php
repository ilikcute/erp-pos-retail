<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_closing_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_closing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_session_id')->constrained('cashier_sessions')->cascadeOnDelete();
            $table->decimal('session_sales', 18, 2)->default(0);
            $table->decimal('session_cash', 18, 2)->default(0);
            $table->integer('session_transactions')->default(0);
            $table->timestamps();

            $table->unique(['day_closing_id', 'cashier_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_closing_sessions');
    }
};
