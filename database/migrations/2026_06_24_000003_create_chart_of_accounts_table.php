<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('account_code')->unique();       // 1-1001, 2-1001, dst
            $table->string('account_name');                 // Kas Toko, Bank BCA, dll
            $table->string('account_type');                 // ASSET, LIABILITY, dll
            $table->string('normal_balance');               // DEBIT, CREDIT
            $table->boolean('is_postable')->default(true);  // Bisa ditransaksikan
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['account_type', 'is_active']);
            $table->index('parent_id');
        });

        // Seed default COA (Indonesian standard)
        $this->seedDefaultAccounts();
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }

    private function seedDefaultAccounts(): void
    {
        $accounts = [
            // ASET (1xxx)
            ['code' => '1-0000', 'name' => 'Aset', 'type' => 'ASSET', 'parent' => null, 'postable' => false],
            ['code' => '1-1000', 'name' => 'Aset Lancar', 'type' => 'ASSET', 'parent' => '1-0000', 'postable' => false],
            ['code' => '1-1001', 'name' => 'Kas Toko', 'type' => 'ASSET', 'parent' => '1-1000', 'postable' => true],
            ['code' => '1-1002', 'name' => 'Bank BCA', 'type' => 'ASSET', 'parent' => '1-1000', 'postable' => true],
            ['code' => '1-1003', 'name' => 'Bank Mandiri', 'type' => 'ASSET', 'parent' => '1-1000', 'postable' => true],
            ['code' => '1-1004', 'name' => 'Piutang Usaha', 'type' => 'ASSET', 'parent' => '1-1000', 'postable' => true],
            ['code' => '1-2000', 'name' => 'Persediaan', 'type' => 'ASSET', 'parent' => '1-0000', 'postable' => true],

            // KEWAJIBAN (2xxx)
            ['code' => '2-0000', 'name' => 'Kewajiban', 'type' => 'LIABILITY', 'parent' => null, 'postable' => false],
            ['code' => '2-1001', 'name' => 'Hutang Usaha', 'type' => 'LIABILITY', 'parent' => '2-0000', 'postable' => true],
            ['code' => '2-1002', 'name' => 'PPN Keluaran', 'type' => 'LIABILITY', 'parent' => '2-0000', 'postable' => true],
            ['code' => '2-1003', 'name' => 'Kewajiban Poin Loyalty', 'type' => 'LIABILITY', 'parent' => '2-0000', 'postable' => true],

            // EKUITAS (3xxx)
            ['code' => '3-0000', 'name' => 'Ekuitas', 'type' => 'EQUITY', 'parent' => null, 'postable' => false],
            ['code' => '3-1001', 'name' => 'Modal Pemilik', 'type' => 'EQUITY', 'parent' => '3-0000', 'postable' => true],

            // PENDAPATAN (4xxx)
            ['code' => '4-0000', 'name' => 'Pendapatan', 'type' => 'REVENUE', 'parent' => null, 'postable' => false],
            ['code' => '4-1001', 'name' => 'Penjualan', 'type' => 'REVENUE', 'parent' => '4-0000', 'postable' => true],
            ['code' => '4-1002', 'name' => 'Diskon Penjualan', 'type' => 'REVENUE', 'parent' => '4-0000', 'postable' => true],

            // BEBAN (5xxx)
            ['code' => '5-0000', 'name' => 'Beban', 'type' => 'EXPENSE', 'parent' => null, 'postable' => false],
            ['code' => '5-1001', 'name' => 'HPP (Harga Pokok Penjualan)', 'type' => 'EXPENSE', 'parent' => '5-0000', 'postable' => true],
            ['code' => '5-1002', 'name' => 'Beban Gaji', 'type' => 'EXPENSE', 'parent' => '5-0000', 'postable' => true],
        ];

        $ids = [];
        foreach ($accounts as $acc) {
            $parentId = $acc['parent'] ? $ids[$acc['parent']] : null;
            $id = \Illuminate\Support\Facades\DB::table('chart_of_accounts')->insertGetId([
                'parent_id' => $parentId,
                'account_code' => $acc['code'],
                'account_name' => $acc['name'],
                'account_type' => $acc['type'],
                'normal_balance' => \App\Enums\Accounting\AccountType::from($acc['type'])->defaultNormalBalance()->value,
                'is_postable' => $acc['postable'],
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $ids[$acc['code']] = $id;
        }
    }
};
