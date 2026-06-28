<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('promotion_code')->unique();
            $table->string('promotion_name');
            $table->text('description')->nullable();
            $table->integer('priority')->default(0); // Higher = applied first
            $table->boolean('stackable')->default(false); // Can combine with other promos
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->string('status')->default('DRAFT');
            $table->boolean('earn_point_allowed')->default(true);
            $table->boolean('redeem_point_allowed')->default(true);
            $table->json('limits')->nullable(); // {max_usage, max_usage_per_customer}
            $table->integer('current_usage')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'valid_from', 'valid_until']);
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
