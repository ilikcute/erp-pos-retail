<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_account_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->unique();

            // Kolom FK ke chart_of_accounts (dihubungkan saat Phase 6, saat ini hanya kolom biasa)
            $table->unsignedBigInteger('inventory_account_id')->nullable();
            $table->unsignedBigInteger('cogs_account_id')->nullable();
            $table->unsignedBigInteger('sales_account_id')->nullable();
            $table->unsignedBigInteger('return_account_id')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_account_mappings');
    }
};
