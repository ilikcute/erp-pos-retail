<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('evaluation_period');
            $table->decimal('on_time_delivery_score', 5, 2)->default(0);
            $table->decimal('price_score', 5, 2)->default(0);
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->decimal('service_score', 5, 2)->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('evaluated_by')->constrained('users');
            $table->timestamps();

            $table->index(['supplier_id', 'evaluation_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_performances');
    }
};
