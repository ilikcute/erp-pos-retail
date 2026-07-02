<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_transactions', 'cashier_session_id')) {
                $table->foreignId('cashier_session_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('cashier_sessions')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('sales_transactions', 'day_closing_id')) {
                $table->foreignId('day_closing_id')
                    ->nullable()
                    ->after('cashier_session_id')
                    ->constrained('day_closings')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropForeign(['day_closing_id']);
            $table->dropColumn('day_closing_id');
            $table->dropForeign(['cashier_session_id']);
            $table->dropColumn('cashier_session_id');
        });
    }
};
