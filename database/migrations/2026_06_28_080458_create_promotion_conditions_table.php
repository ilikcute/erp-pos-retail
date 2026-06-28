<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->string('condition_type'); // MIN_AMOUNT, MIN_QTY, DAY_OF_WEEK, etc
            $table->string('operator')->default('>='); // >=, <=, =, IN
            $table->string('condition_value'); // Value atau JSON array
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('promotion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_conditions');
    }
};
