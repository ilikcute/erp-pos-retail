<?php

namespace Database\Seeders\Performance;

use Carbon\Carbon;
use Database\Seeders\Performance\Concerns\SeedsPerformanceData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BulkSalesTransactionSeeder extends Seeder
{
    use SeedsPerformanceData;

    public function run(): void
    {
        $count = $this->performanceCount();

        $cashierId = DB::table('users')->where('email', 'superadmin@system.local')->value('id') ?? 1;
        $taxId = DB::table('taxes')->where('tax_code', 'PPN11')->value('id');
        $taxRate = 11.00;
        $pcsUnitId = DB::table('units')->where('unit_code', 'PCS')->value('id');
        $shiftId = DB::table('shifts')->value('id') ?? 1;
        $paymentMethodIds = DB::table('payment_methods')->pluck('id')->all();

        /** @var Collection<int, int> $customerIds */
        $customerIds = DB::table('customers')->pluck('id');

        /** @var Collection<int, object> $variants */
        $variants = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('product_barcodes as pb', function ($join) {
                $join->on('pb.product_variant_id', '=', 'pv.id')
                    ->where('pb.is_primary', true);
            })
            ->where('pv.sku', 'like', 'SKU-PERF-%')
            ->orderBy('pv.id')
            ->get([
                'pv.id as variant_id',
                'pv.product_id',
                'pv.sku',
                'p.product_name',
                'pv.purchase_price',
                'pb.barcode',
            ]);

        if ($customerIds->isEmpty() || $variants->isEmpty() || ! $pcsUnitId || $paymentMethodIds === []) {
            $this->command->warn('⚠️  Data prerequisite belum lengkap. Jalankan seeder dasar + BulkProduct/Customer terlebih dahulu.');

            return;
        }

        $sessionCount = max(25, (int) ceil($count / 20));
        $sessionIds = $this->seedSessions($sessionCount, $shiftId, $cashierId);

        $now = now();
        $transactionRows = [];
        $transactionPlans = [];

        for ($i = 1; $i <= $count; $i++) {
            $seq = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $daysAgo = ($i - 1) % 180;
            $transactionDate = Carbon::today()->subDays($daysAgo);
            $sessionId = $sessionIds[($i - 1) % count($sessionIds)];
            $customerId = $customerIds[($i - 1) % $customerIds->count()];
            $isVoid = $i % 20 === 0;
            $itemCount = ($i % 3) + 1;

            $items = [];
            $subtotal = 0.0;

            for ($line = 0; $line < $itemCount; $line++) {
                $variant = $variants[($i + $line) % $variants->count()];
                $qty = (($i + $line) % 5) + 1;
                $unitPrice = round((float) $variant->purchase_price * 1.35, 2);
                $lineSubtotal = round($qty * $unitPrice, 2);
                $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $items[] = [
                    'product_variant_id' => $variant->variant_id,
                    'product_id' => $variant->product_id,
                    'item_name' => $variant->product_name,
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                    'unit_id' => $pcsUnitId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0.00,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                    'cost_price' => (float) $variant->purchase_price,
                ];

                $subtotal += $lineSubtotal;
            }

            $discountAmount = $i % 10 === 0 ? 5_000.00 : 0.00;
            $taxable = max(0, $subtotal - $discountAmount);
            $taxAmount = round($taxable * ($taxRate / 100), 2);
            $grandTotal = round($taxable + $taxAmount, 2);
            $postedAt = $transactionDate->copy()->setTime(8 + ($i % 10), $i % 60, 0);

            $transactionNo = 'PERF-POS-'.$transactionDate->format('Ymd').'-'.$seq;

            $transactionPlans[] = [
                'transaction_no' => $transactionNo,
                'items' => $items,
                'payment_method' => $paymentMethodIds[($i - 1) % count($paymentMethodIds)],
                'grand_total' => $grandTotal,
            ];

            $transactionRows[] = [
                'transaction_no' => $transactionNo,
                'sales_session_id' => $sessionId,
                'cashier_id' => $cashierId,
                'customer_id' => $customerId,
                'transaction_date' => $transactionDate->toDateString(),
                'status' => $isVoid ? 'VOID' : 'POSTED',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $isVoid ? 0.00 : $grandTotal,
                'change_amount' => 0.00,
                'tax_id' => $taxId,
                'tax_rate' => $taxRate,
                'notes' => "Bulk performance transaction #{$i}",
                'posted_by' => $cashierId,
                'posted_at' => $postedAt,
                'voided_by' => $isVoid ? $cashierId : null,
                'voided_at' => $isVoid ? $postedAt->copy()->addMinutes(5) : null,
                'void_reason' => $isVoid ? 'Dummy void for performance test' : null,
                'created_at' => $postedAt,
                'updated_at' => $postedAt,
            ];
        }

        $this->insertInChunks('sales_transactions', $transactionRows);

        $transactionIdByNo = DB::table('sales_transactions')
            ->where('transaction_no', 'like', 'PERF-POS-%')
            ->pluck('id', 'transaction_no');

        $itemRows = [];
        $paymentRows = [];

        foreach ($transactionPlans as $index => $plan) {
            $transactionId = $transactionIdByNo[$plan['transaction_no']] ?? null;

            if (! $transactionId) {
                continue;
            }

            $postedAt = $transactionRows[$index]['posted_at'] ?? $now;

            foreach ($plan['items'] as $item) {
                $itemRows[] = array_merge($item, [
                    'sales_transaction_id' => $transactionId,
                    'created_at' => $postedAt,
                    'updated_at' => $postedAt,
                ]);
            }

            if (($transactionRows[$index]['status'] ?? 'POSTED') === 'POSTED') {
                $paySeq = str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
                $paymentRows[] = [
                    'payment_no' => 'PERF-PAY-'.$paySeq,
                    'sales_transaction_id' => $transactionId,
                    'payment_method_id' => $plan['payment_method'],
                    'amount' => $plan['grand_total'],
                    'reference_no' => $plan['payment_method'] > 1 ? 'REF-PERF-'.$paySeq : null,
                    'status' => 'POSTED',
                    'posted_by' => $cashierId,
                    'posted_at' => $postedAt,
                    'created_at' => $postedAt,
                    'updated_at' => $postedAt,
                ];
            }
        }

        $this->insertInChunks('sales_transaction_items', $itemRows);
        $this->insertInChunks('sales_payments', $paymentRows);

        $this->command->info("✅ {$count} bulk sales transactions + items + payments berhasil di-seed.");
    }

    /**
     * @return array<int, int>
     */
    private function seedSessions(int $sessionCount, int $shiftId, int $cashierId): array
    {
        $existing = DB::table('sales_sessions')
            ->where('session_no', 'like', 'PERF-SS-%')
            ->count();

        if ($existing >= $sessionCount) {
            return DB::table('sales_sessions')
                ->where('session_no', 'like', 'PERF-SS-%')
                ->orderBy('id')
                ->pluck('id')
                ->all();
        }

        $now = now();
        $rows = [];

        for ($i = 1; $i <= $sessionCount; $i++) {
            $seq = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $sessionDate = Carbon::today()->subDays($sessionCount - $i);
            $openedAt = $sessionDate->copy()->setTime(7, 0, 0);

            $rows[] = [
                'session_no' => 'PERF-SS-'.$sessionDate->format('Ymd').'-'.$seq,
                'shift_id' => $shiftId,
                'cashier_id' => $cashierId,
                'session_date' => $sessionDate->toDateString(),
                'status' => 'CLOSED',
                'opening_cash' => 500_000.00,
                'closing_cash' => 2_500_000.00,
                'expected_cash' => 2_500_000.00,
                'cash_difference' => 0.00,
                'total_sales' => 2_000_000.00,
                'total_transactions' => 2_000_000.00,
                'transaction_count' => 20,
                'notes' => "Bulk performance session #{$i}",
                'closed_at' => $sessionDate->copy()->setTime(15, 0, 0),
                'created_at' => $openedAt,
                'updated_at' => $openedAt,
            ];
        }

        $this->insertInChunks('sales_sessions', $rows);

        return DB::table('sales_sessions')
            ->where('session_no', 'like', 'PERF-SS-%')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }
}
