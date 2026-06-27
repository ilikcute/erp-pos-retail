<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class JournalService
{
    /**
     * Buat journal entry dari transaksi POS.
     *
     * Jurnal standar penjualan tunai:
     *   Dr Kas / Bank / QRIS      (debit - aset naik)
     *   Cr Penjualan              (credit - revenue naik)
     *   Cr PPN Keluaran           (jika ada PPN)
     *
     * Jurnal HPP (cost of goods sold):
     *   Dr HPP                    (debit - expense naik)
     *   Cr Persediaan             (credit - aset turun)
     */
    public function createPosTransactionJournal(
        $transaction,
        array $paymentMethods,
        array $options = []
    ): JournalEntry {
        return DB::transaction(function () use ($transaction, $paymentMethods, $options) {
            $journal = JournalEntry::create([
                'journal_number' => $this->generateJournalNumber(),
                'journal_date' => $transaction->created_at ?? now(),
                'source_type' => 'POS_TRANSACTION',
                'source_id' => $transaction->id,
                'description' => "Penjualan POS #{$transaction->id}",
                'status' => 'POSTED',
                'created_by' => $transaction->user_id ?? null,
                'posted_at' => now(),
            ]);

            $lineOrder = 1;

            // ═══════════════════════════════════════════════════════════
            // 1. DEBIT: Aset (per payment method)
            // ═══════════════════════════════════════════════════════════
            foreach ($paymentMethods as $payment) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $payment['account_id'],
                    'debit' => $payment['amount'],
                    'credit' => 0,
                    'description' => "Pembayaran via {$payment['method_name']}",
                    'line_order' => $lineOrder++,
                ]);
            }

            // ═══════════════════════════════════════════════════════════
            // 2. CREDIT: Revenue (Penjualan)
            // ═══════════════════════════════════════════════════════════
            $revenueAccount = ChartOfAccount::where('account_code', '4-1001')->first();
            if ($revenueAccount) {
                $salesAmount = $transaction->subtotal ?? $transaction->grand_total;
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => $salesAmount,
                    'description' => 'Penjualan Barang',
                    'line_order' => $lineOrder++,
                ]);
            }

            // ═══════════════════════════════════════════════════════════
            // 3. CREDIT: PPN Keluaran (jika ada)
            // ═══════════════════════════════════════════════════════════
            if (($transaction->tax ?? 0) > 0) {
                $ppnAccount = ChartOfAccount::where('account_code', '2-1002')->first();
                if ($ppnAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $ppnAccount->id,
                        'debit' => 0,
                        'credit' => $transaction->tax,
                        'description' => 'PPN Keluaran',
                        'line_order' => $lineOrder++,
                    ]);
                }
            }

            // ═══════════════════════════════════════════════════════════
            // 4. CREDIT: Diskon (contra revenue)
            // ═══════════════════════════════════════════════════════════
            if (($transaction->discount ?? 0) > 0) {
                $discountAccount = ChartOfAccount::where('account_code', '4-1002')->first();
                if ($discountAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $discountAccount->id,
                        'debit' => $transaction->discount,
                        'credit' => 0,
                        'description' => 'Diskon Penjualan',
                        'line_order' => $lineOrder++,
                    ]);
                }
            }

            // ═══════════════════════════════════════════════════════════
            // 5. VALIDASI: Debit harus == Credit
            // ═══════════════════════════════════════════════════════════
            if (!$journal->isBalanced()) {
                throw new \DomainException(
                    "Journal tidak balance! Debit: {$journal->lines->sum('debit')}, Credit: {$journal->lines->sum('credit')}"
                );
            }

            return $journal;
        });
    }

    /**
     * Buat jurnal HPP (Cost of Goods Sold)
     */
    public function createCogsJournal($transaction, array $items): JournalEntry
    {
        return DB::transaction(function () use ($transaction, $items) {
            $journal = JournalEntry::create([
                'journal_number' => $this->generateJournalNumber(),
                'journal_date' => now(),
                'source_type' => 'COGS',
                'source_id' => $transaction->id,
                'description' => "HPP Penjualan #{$transaction->id}",
                'status' => 'POSTED',
                'posted_at' => now(),
            ]);

            $hppAccount = ChartOfAccount::where('account_code', '5-1001')->first();
            $inventoryAccount = ChartOfAccount::where('account_code', '1-2000')->first();

            if (!$hppAccount || !$inventoryAccount) {
                throw new \DomainException('Akun HPP atau Persediaan tidak ditemukan');
            }

            $totalCogs = 0;
            foreach ($items as $item) {
                $totalCogs += ($item->unit_cost ?? 0) * ($item->qty ?? 0);
            }

            if ($totalCogs > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $hppAccount->id,
                    'debit' => $totalCogs,
                    'credit' => 0,
                    'description' => 'Harga Pokok Penjualan',
                    'line_order' => 1,
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $inventoryAccount->id,
                    'debit' => 0,
                    'credit' => $totalCogs,
                    'description' => 'Pengurangan Persediaan',
                    'line_order' => 2,
                ]);
            }

            return $journal;
        });
    }

    private function generateJournalNumber(): string
    {
        $date = now()->format('Ymd');
        $last = JournalEntry::whereDate('created_at', today())
            ->where('journal_number', 'like', "JE-{$date}-%")
            ->count();
        return sprintf('JE-%s-%04d', $date, $last + 1);
    }
}
