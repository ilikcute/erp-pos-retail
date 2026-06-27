<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_code')->unique();           // CASH-01, QRIS-01
            $table->string('method_name');                      // Tunai, QRIS
            $table->string('method_type');                      // CASH, QRIS, LOYALTY_POINT, dll
            $table->foreignId('account_id')
                ->constrained('chart_of_accounts')
                ->cascadeOnDelete();
            $table->string('gateway_code')->nullable();         // midtrans, xendit, dll
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['method_type', 'is_active']);
        });

        // Seed default payment methods
        $this->seedDefaultPaymentMethods();
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }

    private function seedDefaultPaymentMethods(): void
    {
        $methods = [
            ['code' => 'CASH-01', 'name' => 'Tunai', 'type' => 'CASH', 'coa_code' => '1-1001', 'sort' => 1],
            ['code' => 'QRIS-01', 'name' => 'QRIS', 'type' => 'QRIS', 'coa_code' => '1-1002', 'sort' => 2],
            ['code' => 'TRF-BCA', 'name' => 'Transfer BCA', 'type' => 'TRANSFER', 'coa_code' => '1-1002', 'sort' => 3],
            ['code' => 'TRF-MDR', 'name' => 'Transfer Mandiri', 'type' => 'TRANSFER', 'coa_code' => '1-1003', 'sort' => 4],
            ['code' => 'POINT-01', 'name' => 'Loyalty Point', 'type' => 'LOYALTY_POINT', 'coa_code' => '2-1003', 'sort' => 5],
        ];

        foreach ($methods as $m) {
            $account = \Illuminate\Support\Facades\DB::table('chart_of_accounts')
                ->where('account_code', $m['coa_code'])
                ->first();

            if ($account) {
                \Illuminate\Support\Facades\DB::table('payment_methods')->insert([
                    'method_code' => $m['code'],
                    'method_name' => $m['name'],
                    'method_type' => $m['type'],
                    'account_id' => $account->id,
                    'is_active' => true,
                    'sort_order' => $m['sort'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
