<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_configurations', function (Blueprint $table) {
            $table->id();
            $table->integer('point_expiry_months')->default(12); // Masa berlaku poin
            $table->integer('minimum_redeem_points')->default(100); // Minimum poin untuk redeem
            $table->integer('point_value')->default(100); // Nilai Rp per 1 poin (100 = Rp 100)
            $table->decimal('earn_rate', 10, 4)->default(1000); // Rp 1000 = 1 poin
            $table->boolean('allow_negative_point')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->text('terms_and_conditions')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Insert default configuration
        \Illuminate\Support\Facades\DB::table('loyalty_configurations')->insert([
            'point_expiry_months' => 12,
            'minimum_redeem_points' => 100,
            'point_value' => 100,
            'earn_rate' => 1000,
            'allow_negative_point' => false,
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_configurations');
    }
};
