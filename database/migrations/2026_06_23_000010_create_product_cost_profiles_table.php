<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_cost_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_id')->unique();
            $table->string('cost_method', 20)->default('FIFO'); // FIFO|AVERAGE
            $table->decimal('standard_cost', 18, 4)->default(0);
            $table->decimal('last_cost', 18, 4)->default(0);
            $table->decimal('average_cost', 18, 4)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cost_profiles');
    }
};
