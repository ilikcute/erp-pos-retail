<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchasingMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding 500+ purchasing & supplier payment records...');

        $supplierIds = DB::table('suppliers')->pluck('id')->toArray();
        if (empty($supplierIds)) {
            $supplierIds = [
                DB::table('suppliers')->insertGetId([
                    'supplier_name' => 'Supplier Utama Mock',
                    'contact_name' => 'Budi',
                    'phone' => '08123456789',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            ];
        }

        $variantIds = DB::table('product_variants')->pluck('id')->toArray();
        if (empty($variantIds)) {
            $productId = DB::table('products')->insertGetId([
                'product_name' => 'Produk Utama Mock',
                'sku' => 'SKU-MOCK',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $variantIds = [
                DB::table('product_variants')->insertGetId([
                    'product_id' => $productId,
                    'product_code' => 'CODE-MOCK',
                    'price' => 10000,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            ];
        }

        $userId = DB::table('users')->value('id') ?? 1;
        
        // Location
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

        // Truncate existing records to prevent collisions/foreign key issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('purchase_request_items')->truncate();
        DB::table('purchase_requests')->truncate();
        DB::table('purchase_order_items')->truncate();
        DB::table('purchase_orders')->truncate();
        DB::table('goods_receipt_items')->truncate();
        DB::table('goods_receipts')->truncate();
        DB::table('supplier_invoice_items')->truncate();
        DB::table('supplier_invoices')->truncate();
        DB::table('accounts_payables')->truncate();
        DB::table('supplier_payment_allocations')->truncate();
        DB::table('supplier_payments')->truncate();
        DB::table('purchase_return_items')->truncate();
        DB::table('purchase_returns')->truncate();
        DB::table('landed_costs')->truncate();
        DB::table('supplier_performances')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Seed 100 Purchase Requests
        $prIds = [];
        $prItemsBatch = [];
        for ($i = 1; $i <= 100; $i++) {
            $prId = DB::table('purchase_requests')->insertGetId([
                'pr_number' => 'PR-' . date('Ymd') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'request_date' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
                'requested_by' => $userId,
                'status' => $i % 5 === 0 ? 'DRAFT' : ($i % 5 === 1 ? 'REJECTED' : 'APPROVED'),
                'remarks' => 'Remarks request nomor ' . $i,
                'approved_by' => $i % 5 > 1 ? $userId : null,
                'approved_at' => $i % 5 > 1 ? now() : null,
                'rejection_notes' => $i % 5 === 1 ? 'Anggaran tidak cukup' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $prIds[] = $prId;

            for ($k = 0; $k < 2; $k++) {
                $prItemsBatch[] = [
                    'purchase_request_id' => $prId,
                    'product_variant_id' => $variantIds[array_rand($variantIds)],
                    'requested_qty' => rand(10, 100),
                    'notes' => 'Catatan item ' . $k,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('purchase_request_items')->insert($prItemsBatch);

        // 2. Seed 120 Purchase Orders
        $poIds = [];
        $poItemsBatch = [];
        for ($i = 1; $i <= 120; $i++) {
            $supplierId = $supplierIds[array_rand($supplierIds)];
            $poId = DB::table('purchase_orders')->insertGetId([
                'po_number' => 'PO-' . date('Ymd') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'purchase_request_id' => $prIds[array_rand($prIds)],
                'supplier_id' => $supplierId,
                'order_date' => Carbon::now()->subDays(rand(1, 20))->toDateString(),
                'expected_date' => Carbon::now()->addDays(rand(2, 10))->toDateString(),
                'status' => $i % 4 === 0 ? 'DRAFT' : ($i % 4 === 1 ? 'APPROVED' : ($i % 4 === 2 ? 'PARTIAL' : 'RECEIVED')),
                'subtotal' => 5000000,
                'discount_amount' => 0,
                'tax_amount' => 500000,
                'total_amount' => 5500000,
                'remarks' => 'Order notes ' . $i,
                'created_by' => $userId,
                'approved_by' => $i % 4 > 0 ? $userId : null,
                'approved_at' => $i % 4 > 0 ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $poIds[] = $poId;

            for ($k = 0; $k < 2; $k++) {
                $poItemsBatch[] = [
                    'purchase_order_id' => $poId,
                    'product_variant_id' => $variantIds[array_rand($variantIds)],
                    'ordered_qty' => 50,
                    'received_qty' => $i % 4 === 3 ? 50 : ($i % 4 === 2 ? 20 : 0),
                    'unit_cost' => 50000,
                    'discount_amount' => 0,
                    'tax_amount' => 5000,
                    'line_total' => 2505000,
                    'notes' => 'Line note ' . $k,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('purchase_order_items')->insert($poItemsBatch);

        // Get inserted purchase order items to reference in goods receipts
        $poItemIds = DB::table('purchase_order_items')->pluck('id')->toArray();

        // 3. Seed 80 Goods Receipts
        $grIds = [];
        $grItemsBatch = [];
        for ($i = 1; $i <= 80; $i++) {
            $grId = DB::table('goods_receipts')->insertGetId([
                'gr_number' => 'GR-' . date('Ymd') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'purchase_order_id' => $poIds[array_rand($poIds)],
                'location_id' => $locationId,
                'receipt_date' => Carbon::now()->subDays(rand(1, 15))->toDateString(),
                'status' => $i % 2 === 0 ? 'DRAFT' : 'POSTED',
                'remarks' => 'Penerimaan barang ' . $i,
                'received_by' => $userId,
                'posted_by' => $i % 2 !== 0 ? $userId : null,
                'posted_at' => $i % 2 !== 0 ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $grIds[] = $grId;

            for ($k = 0; $k < 2; $k++) {
                $grItemsBatch[] = [
                    'goods_receipt_id' => $grId,
                    'purchase_order_item_id' => $poItemIds[array_rand($poItemIds)],
                    'product_variant_id' => $variantIds[array_rand($variantIds)],
                    'received_qty' => 25,
                    'unit_cost' => 50000,
                    'batch_no' => 'BCH-' . date('Ymd') . '-' . rand(1000, 9999),
                    'expiry_date' => Carbon::now()->addYear()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('goods_receipt_items')->insert($grItemsBatch);

        // 4. Seed 60 Supplier Invoices & Accounts Payables
        $apIds = [];
        for ($i = 1; $i <= 60; $i++) {
            $supplierId = $supplierIds[array_rand($supplierIds)];
            $invoiceId = DB::table('supplier_invoices')->insertGetId([
                'invoice_number' => 'SI-' . date('Ymd') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'supplier_id' => $supplierId,
                'goods_receipt_id' => $grIds[array_rand($grIds)],
                'supplier_invoice_no' => 'INV-SUP-' . rand(10000, 99999),
                'invoice_date' => Carbon::now()->subDays(rand(5, 25))->toDateString(),
                'due_date' => Carbon::now()->addDays(rand(5, 25))->toDateString(),
                'status' => $i % 2 === 0 ? 'DRAFT' : 'UNPAID',
                'subtotal' => 2000000,
                'discount_amount' => 0,
                'tax_amount' => 200000,
                'total_amount' => 2200000,
                'paid_amount' => 0,
                'created_by' => $userId,
                'posted_by' => $i % 2 !== 0 ? $userId : null,
                'posted_at' => $i % 2 !== 0 ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('supplier_invoice_items')->insert([
                [
                    'supplier_invoice_id' => $invoiceId,
                    'product_variant_id' => $variantIds[array_rand($variantIds)],
                    'qty' => 40,
                    'unit_cost' => 50000,
                    'tax_amount' => 200000,
                    'line_total' => 2200000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            if ($i % 2 !== 0) {
                // Also create AP
                $apId = DB::table('accounts_payables')->insertGetId([
                    'payable_number' => 'AP-' . date('Ymd') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'supplier_id' => $supplierId,
                    'invoice_id' => $invoiceId,
                    'source_type' => 'App\Models\Purchasing\SupplierInvoice',
                    'source_id' => $invoiceId,
                    'transaction_date' => Carbon::now()->subDays(10)->toDateString(),
                    'due_date' => Carbon::now()->addDays(20)->toDateString(),
                    'total_amount' => 2200000,
                    'paid_amount' => 0,
                    'remaining_amount' => 2200000,
                    'status' => 'OPEN',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $apIds[] = $apId;
            }
        }

        // 5. Seed 30 Supplier Payments & allocations
        for ($i = 1; $i <= 30; $i++) {
            $supplierId = $supplierIds[array_rand($supplierIds)];
            $payId = DB::table('supplier_payments')->insertGetId([
                'payment_number' => 'SP-' . date('Ymd') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'supplier_id' => $supplierId,
                'payment_date' => Carbon::now()->subDays(rand(1, 5))->toDateString(),
                'payment_method' => 'TRANSFER',
                'reference_no' => 'REF-' . rand(100000, 999999),
                'total_amount' => 1000000,
                'status' => $i % 2 === 0 ? 'DRAFT' : 'POSTED',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Try to find open AP for this supplier to allocate
            $apId = DB::table('accounts_payables')->where('supplier_id', $supplierId)->value('id');
            if ($apId) {
                DB::table('supplier_payment_allocations')->insert([
                    'supplier_payment_id' => $payId,
                    'account_payable_id' => $apId,
                    'allocated_amount' => 1000000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 6. Seed 20 Supplier Performance Evaluations
        for ($i = 1; $i <= 20; $i++) {
            $supplierId = $supplierIds[array_rand($supplierIds)];
            DB::table('supplier_performances')->insert([
                'supplier_id' => $supplierId,
                'evaluation_period' => Carbon::now()->subMonths(rand(1, 6))->startOfMonth()->toDateString(),
                'on_time_delivery_score' => rand(70, 100),
                'price_score' => rand(70, 100),
                'quality_score' => rand(75, 100),
                'service_score' => rand(80, 100),
                'overall_score' => rand(75, 98),
                'notes' => 'Evaluasi bulan ke-' . $i,
                'evaluated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeding finished successfully! Inserted 700+ records.');
    }
}
