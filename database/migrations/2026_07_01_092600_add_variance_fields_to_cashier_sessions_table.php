<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashier_sessions', function (Blueprint $table) {
            $table->decimal('reimbursement_amount', 18, 2)->default(0)->after('cash_difference');
            $table->text('variance_reason')->nullable()->after('reimbursement_amount');
        });
    }

    public function down(): void
    {
        Schema::table('cashier_sessions', function (Blueprint $table) {
            $table->dropColumn(['reimbursement_amount', 'variance_reason']);
        });
    }
};
