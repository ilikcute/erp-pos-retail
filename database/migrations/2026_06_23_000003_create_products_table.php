<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 50)->unique();
            $table->string('product_name', 200);
            $table->string('product_type', 30)->default('SIMPLE'); // SIMPLE|VARIANT|BUNDLE
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('base_unit_id');
            $table->text('description')->nullable();
            $table->string('short_description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sellable')->default(true);
            $table->boolean('is_purchasable')->default(true);
            $table->boolean('track_stock')->default(true);
            $table->decimal('min_stock', 18, 4)->nullable();
            $table->decimal('max_stock', 18, 4)->nullable();
            $table->decimal('reorder_point', 18, 4)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('brand_id')->references('id')->on('product_brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('set null');
            $table->foreign('base_unit_id')->references('id')->on('units');

            $table->index('is_active');
            $table->index('is_sellable');
            $table->index('is_purchasable');
            $table->index('product_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
