<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_category_price_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('customer_category_id');
            $table->timestamps();

            $table->foreign('price_list_id', 'ccpl_price_list_fk')
                ->references('id')->on('price_lists')->onDelete('cascade');
            $table->foreign('customer_category_id', 'ccpl_category_fk')
                ->references('id')->on('customer_categories')->onDelete('cascade');

            $table->unique(['price_list_id', 'customer_category_id'], 'uk_ccpl');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_category_price_lists');
    }
};
