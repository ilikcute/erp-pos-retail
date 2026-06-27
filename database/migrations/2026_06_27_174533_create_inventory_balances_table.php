<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->decimal('qty_on_hand', 15, 2)->default(0);
            $table->decimal('qty_reserved', 15, 2)->default(0); // reserved for pending orders
            $table->decimal('qty_available', 15, 2)->default(0); // on_hand - reserved
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            // Satu variant = satu balance per lokasi
            $table->unique(['product_variant_id', 'location_id'], 'uniq_variant_location');
            $table->index('location_id');
            $table->index('qty_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};
