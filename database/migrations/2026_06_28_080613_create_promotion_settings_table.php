<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_settings', function (Blueprint $table) {
            $table->id();
            $table->string('margin_protection_mode')->default('WARNING');
            $table->boolean('allow_negative_margin')->default(false);
            $table->boolean('allow_stacking')->default(false);
            $table->integer('max_stacking_promotions')->default(3);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('promotion_settings')->insert([
            'margin_protection_mode' => 'WARNING',
            'allow_negative_margin' => false,
            'allow_stacking' => false,
            'max_stacking_promotions' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_settings');
    }
};
