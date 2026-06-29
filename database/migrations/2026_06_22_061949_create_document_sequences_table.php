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
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_code')->nullable()->unique();
            $table->string('document_name')->nullable();
            $table->string('prefix')->nullable();
            $table->string('reset_period')->default('NONE'); // NONE, DAILY, MONTHLY, YEARLY
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedInteger('current_number')->default(0);
            $table->date('last_reset_at')->nullable();
            $table->string('period', 20);                   // e.g. 20260621
            $table->unsignedBigInteger('last_sequence')->default(0);
            $table->timestamps();

            $table->unique(['document_type_id', 'period']);
            $table->foreign('document_type_id')->references('id')->on('document_types')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
