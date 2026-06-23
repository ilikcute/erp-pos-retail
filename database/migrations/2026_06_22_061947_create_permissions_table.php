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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();          // e.g. pos.transaction.create
            $table->string('module', 50);                   // e.g. pos
            $table->string('resource', 50);                 // e.g. transaction
            $table->string('action', 50);                   // e.g. create
            $table->string('display_name', 150);
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['module', 'resource', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
