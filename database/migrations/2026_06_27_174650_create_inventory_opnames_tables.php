<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number')->unique();
            $table->foreignId('inventory_location_id')->constrained('inventory_locations');
            $table->date('opname_date');
            $table->string('status')->default('DRAFT');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'opname_date']);
        });

        Schema::create('inventory_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_id')->constrained('inventory_opnames')->cascadeOnDelete();
            $table->foreignId('inventory_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->decimal('system_qty', 15, 2); // qty di sistem saat opname dimulai
            $table->decimal('physical_qty', 15, 2)->nullable(); // hasil hitung fisik
            $table->decimal('difference', 15, 2)->default(0); // physical - system
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_opname_items');
        Schema::dropIfExists('inventory_opnames');
    }
};
