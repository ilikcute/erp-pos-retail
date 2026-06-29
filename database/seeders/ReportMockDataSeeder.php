<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Menjalankan ReportMockDataSeeder untuk halaman Reporting...');

        // 1. Data Dummy untuk Purchase Orders
        $supplierId = DB::table('suppliers')->value('id') ?? 1;
        $adminId = DB::table('users')->where('email', 'like', '%admin%')->value('id') ?? 1;

        $pos = [
            ['po_number' => 'PO-2606-001', 'supplier_id' => $supplierId, 'order_date' => Carbon::now()->subDays(4)->toDateString(), 'expected_date' => Carbon::now()->addDays(2)->toDateString(), 'status' => 'COMPLETED', 'total_amount' => 12500000, 'created_by' => $adminId, 'created_at' => now(), 'updated_at' => now()],
            ['po_number' => 'PO-2606-002', 'supplier_id' => $supplierId, 'order_date' => Carbon::now()->subDays(3)->toDateString(), 'expected_date' => Carbon::now()->addDays(3)->toDateString(), 'status' => 'PENDING', 'total_amount' => 8200000, 'created_by' => $adminId, 'created_at' => now(), 'updated_at' => now()],
            ['po_number' => 'PO-2606-003', 'supplier_id' => $supplierId, 'order_date' => Carbon::now()->subDays(2)->toDateString(), 'expected_date' => Carbon::now()->addDays(4)->toDateString(), 'status' => 'COMPLETED', 'total_amount' => 24000000, 'created_by' => $adminId, 'created_at' => now(), 'updated_at' => now()],
            ['po_number' => 'PO-2606-004', 'supplier_id' => $supplierId, 'order_date' => Carbon::now()->subDays(1)->toDateString(), 'expected_date' => Carbon::now()->addDays(5)->toDateString(), 'status' => 'PARTIAL', 'total_amount' => 5400000, 'created_by' => $adminId, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($pos as $po) {
            DB::table('purchase_orders')->updateOrInsert(['po_number' => $po['po_number']], $po);
        }

        // 2. Data Dummy untuk Inventory Balances & Inventory Ledgers
        $variantId1 = DB::table('product_variants')->value('id') ?? 1;
        $variantId2 = DB::table('product_variants')->orderBy('id', 'desc')->value('id') ?? 2;
        $locationId = DB::table('inventory_locations')->value('id');
        if (!$locationId) {
            $locationId = DB::table('inventory_locations')->insertGetId([
                'name' => 'Main Warehouse',
                'code' => 'WH-01',
                'type' => 'WAREHOUSE',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $inventory = [
            ['product_variant_id' => $variantId1, 'location_id' => $locationId, 'qty_on_hand' => 5, 'qty_reserved' => 0, 'qty_available' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_variant_id' => $variantId2, 'location_id' => $locationId, 'qty_on_hand' => 8, 'qty_reserved' => 0, 'qty_available' => 8, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($inventory as $inv) {
            DB::table('inventory_balances')->updateOrInsert(
                ['product_variant_id' => $inv['product_variant_id'], 'location_id' => $inv['location_id']],
                $inv
            );
        }
        
        // Ledger summary data so the movement shows up
        DB::table('inventory_ledgers')->updateOrInsert(
            ['reference_number' => 'MOCK-IN-1'],
            ['transaction_type' => 'RECEIPT', 'product_variant_id' => $variantId1, 'location_id' => $locationId, 'qty_change' => 50, 'qty_before' => 0, 'qty_after' => 50, 'unit_cost' => 22000, 'transaction_date' => now()->startOfMonth(), 'reference_type' => 'App\Models\Purchasing\PurchaseOrder', 'reference_id' => 1, 'user_id' => $adminId]
        );
        DB::table('inventory_ledgers')->updateOrInsert(
            ['reference_number' => 'MOCK-OUT-1'],
            ['transaction_type' => 'SALE', 'product_variant_id' => $variantId2, 'location_id' => $locationId, 'qty_change' => -12, 'qty_before' => 20, 'qty_after' => 8, 'unit_cost' => 6000, 'transaction_date' => now()->startOfMonth(), 'reference_type' => 'App\Models\POS\SalesTransaction', 'reference_id' => 1, 'user_id' => $adminId]
        );

        // 3. Data Dummy untuk Chart Of Accounts & Journal Entries (Financials)
        $coa = [
            ['account_code' => '4-100', 'account_name' => 'Pendapatan Penjualan', 'account_type' => 'REVENUE', 'normal_balance' => 'CREDIT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['account_code' => '5-100', 'account_name' => 'Harga Pokok Penjualan', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['account_code' => '6-100', 'account_name' => 'Beban Operasional', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['account_code' => '1-100', 'account_name' => 'Kas & Bank', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['account_code' => '1-200', 'account_name' => 'Persediaan', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['account_code' => '2-100', 'account_name' => 'Hutang Usaha', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['account_code' => '3-100', 'account_name' => 'Modal Pemilik', 'account_type' => 'EQUITY', 'normal_balance' => 'CREDIT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($coa as $c) {
            DB::table('chart_of_accounts')->updateOrInsert(['account_code' => $c['account_code']], $c);
        }

        // To make GenerateFinancialReportAction work, we actually just need balances. 
        // Delete existing mock entries to ensure idempotency
        DB::table('journal_entries')->where('journal_number', 'JE-MOCK-001')->delete();
        DB::table('fiscal_periods')->where('period_name', 'Agustus 2024')->delete();
        DB::table('journal_templates')->where('template_code', 'TMPL-SALE')->delete();
        DB::table('accounting_rules')->where('rule_name', 'Auto Journal Penjualan Kasir')->delete();

        // Let's insert Journal Entries!
        $journalId = DB::table('journal_entries')->insertGetId([
            'journal_number' => 'JE-MOCK-001',
            'journal_date' => now()->startOfMonth(),
            'source_type' => 'MOCK',
            'source_id' => 1,
            'status' => 'POSTED',
            'created_by' => $adminId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $items = [
            ['account_code' => '4-100', 'debit' => 0, 'credit' => 184500000],
            ['account_code' => '5-100', 'debit' => 106000000, 'credit' => 0],
            ['account_code' => '6-100', 'debit' => 36200000, 'credit' => 0],
            ['account_code' => '1-100', 'debit' => 95000000, 'credit' => 0],
            ['account_code' => '1-200', 'debit' => 217500000, 'credit' => 0],
            ['account_code' => '2-100', 'debit' => 0, 'credit' => 78000000],
            ['account_code' => '3-100', 'debit' => 0, 'credit' => 234500000],
        ];

        foreach ($items as $item) {
            $accId = DB::table('chart_of_accounts')->where('account_code', $item['account_code'])->value('id');
            if ($accId) {
                DB::table('journal_entry_lines')->insert([
                    'journal_entry_id' => $journalId,
                    'account_id' => $accId,
                    'debit' => $item['debit'],
                    'credit' => $item['credit'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 4. Data Dummy untuk Periode Fiskal
        $periodId = DB::table('fiscal_periods')->insertGetId([
            'period_name' => 'Agustus 2024',
            'start_date' => '2024-08-01',
            'end_date' => '2024-08-31',
            'status' => 'OPEN',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Data Dummy untuk Journal Templates & lines
        $cashAccId = DB::table('chart_of_accounts')->where('account_code', '1-100')->value('id') ?? 1;
        $revAccId = DB::table('chart_of_accounts')->where('account_code', '4-100')->value('id') ?? 1;

        $tmplId = DB::table('journal_templates')->insertGetId([
            'template_code' => 'TMPL-SALE',
            'template_name' => 'Template Jurnal Penjualan',
            'event_type' => 'SALE',
            'description' => 'Auto-journal untuk setiap transaksi penjualan',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('journal_template_lines')->insert([
            [
                'journal_template_id' => $tmplId,
                'account_id' => $cashAccId,
                'direction' => 'DEBIT',
                'formula' => 'grand_total',
                'description' => 'Kas / Bank',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'journal_template_id' => $tmplId,
                'account_id' => $revAccId,
                'direction' => 'CREDIT',
                'formula' => 'grand_total',
                'description' => 'Pendapatan Penjualan',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 6. Data Dummy untuk Accounting Rules
        DB::table('accounting_rules')->insert([
            'rule_name' => 'Auto Journal Penjualan Kasir',
            'event_type' => 'SALE',
            'journal_template_id' => $tmplId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
