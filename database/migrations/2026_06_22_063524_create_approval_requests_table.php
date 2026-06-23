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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('approval_type_id')->constrained();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('current_level')->default(1);
            $table->string('status', 20)->default('PENDING'); // PENDING, APPROVED, REJECTED, CANCELLED
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->decimal('amount', 18, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
