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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('module_name')->nullable(); // POS, INVENTORY, PURCHASING, dll
            $table->string('action')->nullable(); // LOGIN, CREATE, UPDATE, DELETE, EXPORT, dll
            $table->nullableMorphs('subject'); // Model yang di-affect
            $table->json('properties')->nullable(); // Old & new values
            $table->string('activity', 100);
            $table->string('module', 50);
            $table->string('description')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('module');
            $table->index('module_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
