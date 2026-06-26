<?php

namespace Database\Seeders\POS;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $superadminId = DB::table('users')->where('email', 'superadmin@system.local')->value('id') ?? 1;
        $taxId = DB::table('taxes')->where('tax_code', 'PPN11')->value('id');
        $pcsUnitId = DB::table('units')->where('unit_code', 'PCS')->value('id');

        // Fetch products and variants
        $indomieVar = DB::table('product_variants')->where('sku', 'SKU-IND-GOR')->first();
        $indomieProd = DB::table('products')->where('product_code', 'IND-GOR')->first();

        $cokeVar = DB::table('product_variants')->where('sku', 'SKU-COKE-330')->first();
        $cokeProd = DB::table('products')->where('product_code', 'COKE-330')->first();

        $roseVar = DB::table('product_variants')->where('sku', 'SKU-SBN-ROSE')->first();
        $roseProd = DB::table('products')->where('product_code', 'SBN-MND')->first();

        // ═══════════════════════════════════════════════════════════════
        // TRANSAKSI 1: RETAIL - TUNAI - POSTED
        // ═══════════════════════════════════════════════════════════════
        if ($indomieVar && $cokeVar) {
            $transId1 = DB::table('sales_transactions')->insertGetId([
                'transaction_no' => 'POS-20260624-0001',
                'sales_session_id' => 1, // Sesi tutup kemarin
                'cashier_id' => $superadminId,
                'customer_id' => 1, // Pelanggan Umum
                'transaction_date' => '2026-06-24',
                'status' => 'POSTED',
                'subtotal' => 13000.00, // (3500 * 2) + 6000
                'discount_amount' => 0.00,
                'tax_amount' => 1430.00, // 11% dari 13000
                'grand_total' => 14430.00,
                'paid_amount' => 15000.00,
                'change_amount' => 570.00,
                'tax_id' => $taxId,
                'tax_rate' => 11.00,
                'notes' => 'Transaksi retail kas pertama',
                'posted_by' => $superadminId,
                'posted_at' => '2026-06-24 09:30:00',
                'created_at' => '2026-06-24 09:30:00',
                'updated_at' => '2026-06-24 09:30:00',
            ]);

            // Item 1: Indomie Goreng (qty 2)
            $itemId1 = DB::table('sales_transaction_items')->insertGetId([
                'sales_transaction_id' => $transId1,
                'product_variant_id' => $indomieVar->id,
                'product_id' => $indomieProd->id,
                'item_name' => $indomieProd->product_name,
                'sku' => $indomieVar->sku,
                'barcode' => '8886013850111',
                'unit_id' => $pcsUnitId,
                'quantity' => 2.0000,
                'unit_price' => 3500.00,
                'discount_amount' => 0.00,
                'tax_amount' => 770.00, // 11% dari 7000
                'line_total' => 7770.00,
                'cost_price' => 2500.00,
                'created_at' => '2026-06-24 09:30:00',
                'updated_at' => '2026-06-24 09:30:00',
            ]);

            // Item 2: Coca Cola (qty 1)
            $itemId2 = DB::table('sales_transaction_items')->insertGetId([
                'sales_transaction_id' => $transId1,
                'product_variant_id' => $cokeVar->id,
                'product_id' => $cokeProd->id,
                'item_name' => $cokeProd->product_name,
                'sku' => $cokeVar->sku,
                'barcode' => '8886000101011',
                'unit_id' => $pcsUnitId,
                'quantity' => 1.0000,
                'unit_price' => 6000.00,
                'discount_amount' => 0.00,
                'tax_amount' => 660.00, // 11% dari 6000
                'line_total' => 6660.00,
                'cost_price' => 4000.00,
                'created_at' => '2026-06-24 09:30:00',
                'updated_at' => '2026-06-24 09:30:00',
            ]);

            // Payment: Cash
            DB::table('sales_payments')->insert([
                'payment_no' => 'PAY-20260624-0001',
                'sales_transaction_id' => $transId1,
                'payment_method_id' => 1, // CASH
                'amount' => 14430.00,
                'reference_no' => null,
                'status' => 'POSTED',
                'posted_by' => $superadminId,
                'posted_at' => '2026-06-24 09:30:00',
                'created_at' => '2026-06-24 09:30:00',
                'updated_at' => '2026-06-24 09:30:00',
            ]);
        }

        // ═══════════════════════════════════════════════════════════════
        // TRANSAKSI 2: GROSIR - QRIS - POSTED DENGAN DISKON
        // ═══════════════════════════════════════════════════════════════
        if ($indomieVar && $cokeVar) {
            $transId2 = DB::table('sales_transactions')->insertGetId([
                'transaction_no' => 'POS-20260624-0002',
                'sales_session_id' => 1,
                'cashier_id' => $superadminId,
                'customer_id' => 2, // Budi Santoso (Wholesale)
                'transaction_date' => '2026-06-24',
                'status' => 'POSTED',
                'subtotal' => 63000.00, // (3000 * 10) + (5500 * 6)
                'discount_amount' => 3000.00, // Diskon manual
                'tax_amount' => 6600.00, // 11% dari (63000 - 3000)
                'grand_total' => 66600.00,
                'paid_amount' => 66600.00,
                'change_amount' => 0.00,
                'tax_id' => $taxId,
                'tax_rate' => 11.00,
                'notes' => 'Pembelian grosir reseller Budi',
                'posted_by' => $superadminId,
                'posted_at' => '2026-06-24 10:15:00',
                'created_at' => '2026-06-24 10:15:00',
                'updated_at' => '2026-06-24 10:15:00',
            ]);

            // Item 1: Indomie Goreng (qty 10)
            $itemWholesale1 = DB::table('sales_transaction_items')->insertGetId([
                'sales_transaction_id' => $transId2,
                'product_variant_id' => $indomieVar->id,
                'product_id' => $indomieProd->id,
                'item_name' => $indomieProd->product_name,
                'sku' => $indomieVar->sku,
                'barcode' => '8886013850111',
                'unit_id' => $pcsUnitId,
                'quantity' => 10.0000,
                'unit_price' => 3000.00, // Wholesale price
                'discount_amount' => 0.00,
                'tax_amount' => 3300.00,
                'line_total' => 33300.00,
                'cost_price' => 2500.00,
                'created_at' => '2026-06-24 10:15:00',
                'updated_at' => '2026-06-24 10:15:00',
            ]);

            // Item 2: Coca Cola (qty 6)
            $itemWholesale2 = DB::table('sales_transaction_items')->insertGetId([
                'sales_transaction_id' => $transId2,
                'product_variant_id' => $cokeVar->id,
                'product_id' => $cokeProd->id,
                'item_name' => $cokeProd->product_name,
                'sku' => $cokeVar->sku,
                'barcode' => '8886000101011',
                'unit_id' => $pcsUnitId,
                'quantity' => 6.0000,
                'unit_price' => 5500.00, // Wholesale price
                'discount_amount' => 0.00,
                'tax_amount' => 3630.00,
                'line_total' => 36630.00,
                'cost_price' => 4000.00,
                'created_at' => '2026-06-24 10:15:00',
                'updated_at' => '2026-06-24 10:15:00',
            ]);

            // Sales Discount: Diskon Global 3000
            DB::table('sales_discounts')->insert([
                'sales_transaction_id' => $transId2,
                'sales_transaction_item_id' => null,
                'discount_type' => 'MANUAL',
                'discount_value' => 3000.00,
                'discount_amount' => 3000.00,
                'description' => 'Potongan grosir manual',
                'created_at' => '2026-06-24 10:15:00',
                'updated_at' => '2026-06-24 10:15:00',
            ]);

            // Payment: QRIS
            DB::table('sales_payments')->insert([
                'payment_no' => 'PAY-20260624-0002',
                'sales_transaction_id' => $transId2,
                'payment_method_id' => 2, // QRIS
                'amount' => 66600.00,
                'reference_no' => 'QRIS-REF-778899',
                'status' => 'POSTED',
                'posted_by' => $superadminId,
                'posted_at' => '2026-06-24 10:15:00',
                'created_at' => '2026-06-24 10:15:00',
                'updated_at' => '2026-06-24 10:15:00',
            ]);
        }

        // ═══════════════════════════════════════════════════════════════
        // TRANSAKSI 3: RETAIL - VOID
        // ═══════════════════════════════════════════════════════════════
        if ($roseVar) {
            $transId3 = DB::table('sales_transactions')->insertGetId([
                'transaction_no' => 'POS-20260624-0003',
                'sales_session_id' => 1,
                'cashier_id' => $superadminId,
                'customer_id' => 1,
                'transaction_date' => '2026-06-24',
                'status' => 'VOID',
                'subtotal' => 5000.00,
                'discount_amount' => 0.00,
                'tax_amount' => 550.00,
                'grand_total' => 5550.00,
                'paid_amount' => 0.00,
                'change_amount' => 0.00,
                'tax_id' => $taxId,
                'tax_rate' => 11.00,
                'notes' => 'Transaksi salah klik barang',
                'posted_by' => $superadminId,
                'posted_at' => '2026-06-24 11:00:00',
                'voided_by' => $superadminId,
                'voided_at' => '2026-06-24 11:02:00',
                'void_reason' => 'Pembatalan sebelum pembayaran',
                'created_at' => '2026-06-24 11:00:00',
                'updated_at' => '2026-06-24 11:02:00',
            ]);

            DB::table('sales_transaction_items')->insert([
                'sales_transaction_id' => $transId3,
                'product_variant_id' => $roseVar->id,
                'product_id' => $roseProd->id,
                'item_name' => $roseProd->product_name,
                'sku' => $roseVar->sku,
                'barcode' => '8882000000011',
                'unit_id' => $pcsUnitId,
                'quantity' => 1.0000,
                'unit_price' => 5000.00,
                'discount_amount' => 0.00,
                'tax_amount' => 550.00,
                'line_total' => 5550.00,
                'cost_price' => 3000.00,
                'created_at' => '2026-06-24 11:00:00',
                'updated_at' => '2026-06-24 11:00:00',
            ]);

            DB::table('sales_voids')->insert([
                'void_no' => 'VD-20260624-0001',
                'sales_transaction_id' => $transId3,
                'status' => 'APPROVED',
                'reason' => 'Salah klik sabun rose, aslinya mau lemon',
                'requested_by' => $superadminId,
                'approved_by' => $superadminId,
                'requested_at' => '2026-06-24 11:02:00',
                'approved_at' => '2026-06-24 11:02:00',
                'created_at' => '2026-06-24 11:02:00',
                'updated_at' => '2026-06-24 11:02:00',
            ]);
        }

        // ═══════════════════════════════════════════════════════════════
        // TRANSAKSI 4: RETUR PENJUALAN
        // ═══════════════════════════════════════════════════════════════
        if (isset($transId1) && $indomieVar) {
            $returnId = DB::table('sales_returns')->insertGetId([
                'return_no' => 'RTN-20260624-0001',
                'sales_transaction_id' => $transId1,
                'cashier_id' => $superadminId,
                'customer_id' => 1,
                'return_date' => '2026-06-24',
                'status' => 'POSTED',
                'subtotal' => 3500.00, // Retur 1 bungkus Indomie
                'tax_amount' => 385.00,  // PPN 11%
                'total_amount' => 3885.00,
                'reason' => 'Mie instan kemasan robek',
                'posted_by' => $superadminId,
                'posted_at' => '2026-06-24 14:00:00',
                'created_at' => '2026-06-24 14:00:00',
                'updated_at' => '2026-06-24 14:00:00',
            ]);

            $transItem = DB::table('sales_transaction_items')
                ->where('sales_transaction_id', $transId1)
                ->where('product_variant_id', $indomieVar->id)
                ->first();

            if ($transItem) {
                DB::table('sales_return_items')->insert([
                    'sales_return_id' => $returnId,
                    'sales_transaction_item_id' => $transItem->id,
                    'product_variant_id' => $indomieVar->id,
                    'product_id' => $indomieProd->id,
                    'item_name' => $indomieProd->product_name,
                    'unit_id' => $pcsUnitId,
                    'quantity' => 1.0000,
                    'unit_price' => 3500.00,
                    'tax_amount' => 385.00,
                    'line_total' => 3885.00,
                    'reason' => 'Bungkus bocor/robek',
                    'created_at' => '2026-06-24 14:00:00',
                    'updated_at' => '2026-06-24 14:00:00',
                ]);
            }
        }

        // ═══════════════════════════════════════════════════════════════
        // TRANSAKSI 5: HOLD BILL (SESSION 2 - HARI INI)
        // ═══════════════════════════════════════════════════════════════
        if ($indomieVar && $roseVar) {
            $holdId = DB::table('sales_holds')->insertGetId([
                'hold_no' => 'HLD-20260625-0001',
                'sales_session_id' => 2, // Sesi open hari ini
                'cashier_id' => $superadminId,
                'customer_id' => 3, // VIP Customer Siti
                'status' => 'HELD',
                'subtotal' => 8500.00, // 3500 (Indomie) + 5000 (Sabun Rose)
                'discount_amount' => 0.00,
                'tax_amount' => 935.00,
                'grand_total' => 9435.00,
                'notes' => 'Pelanggan ingin mengambil dompet tertinggal',
                'held_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('sales_hold_items')->insert([
                [
                    'sales_hold_id' => $holdId,
                    'product_variant_id' => $indomieVar->id,
                    'product_id' => $indomieProd->id,
                    'item_name' => $indomieProd->product_name,
                    'sku' => $indomieVar->sku,
                    'barcode' => '8886013850111',
                    'unit_id' => $pcsUnitId,
                    'quantity' => 1.0000,
                    'unit_price' => 3500.00,
                    'discount_amount' => 0.00,
                    'tax_amount' => 385.00,
                    'line_total' => 3885.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'sales_hold_id' => $holdId,
                    'product_variant_id' => $roseVar->id,
                    'product_id' => $roseProd->id,
                    'item_name' => $roseProd->product_name,
                    'sku' => $roseVar->sku,
                    'barcode' => '8882000000011',
                    'unit_id' => $pcsUnitId,
                    'quantity' => 1.0000,
                    'unit_price' => 5000.00,
                    'discount_amount' => 0.00,
                    'tax_amount' => 550.00,
                    'line_total' => 5550.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $this->command->info('✅ Sales Transactions, Items, Payments, Voids, Returns, and Hold Bills berhasil di-seed.');
    }
}
