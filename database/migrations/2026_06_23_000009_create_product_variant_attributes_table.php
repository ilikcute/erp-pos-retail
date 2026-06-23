<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id');

            $table->primary(['product_variant_id', 'attribute_value_id'], 'pva_primary');
            
            $table->foreign('product_variant_id', 'pva_variant_fk')
                ->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('attribute_id', 'pva_attribute_fk')
                ->references('id')->on('product_attributes')->onDelete('cascade');
            $table->foreign('attribute_value_id', 'pva_value_fk')
                ->references('id')->on('product_attribute_values')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_attributes');
    }
};
