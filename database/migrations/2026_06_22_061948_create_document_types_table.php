<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();           // e.g. SALES_TRANSACTION
            $table->string('name', 100);
            $table->string('prefix', 20)->nullable();       // e.g. POS
            $table->string('suffix', 20)->nullable();
            $table->string('date_format', 20)->nullable();  // e.g. Ymd
            $table->unsignedTinyInteger('padding')->default(4);
            $table->string('separator', 5)->default('-');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
