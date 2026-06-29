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
        Schema::table('inventory_locations', function (Blueprint $table) {
            $table->boolean('is_stock_bearing')->default(true)->after('type');
            $table->boolean('is_external')->default(false)->after('is_stock_bearing');
            $table->date('valid_from')->nullable()->after('address');
            $table->date('valid_to')->nullable()->after('valid_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_locations', function (Blueprint $table) {
            $table->dropColumn(['is_stock_bearing', 'is_external', 'valid_from', 'valid_to']);
        });
    }
};
