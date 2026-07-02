<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Rap2hpoutre\FastExcel\FastExcel;

class SalesReportExport
{
    public function __construct(private array $data) {}

    public function export(string $filePath): void
    {
        $rows = new Collection;

        // Sheet 1 data: transactions
        foreach ($this->data['transactions'] ?? [] as $tx) {
            $rows->push([
                'No. Transaksi' => $tx->transaction_no ?? '-',
                'Tanggal' => $tx->transaction_date ?? '-',
                'Kasir' => $tx->cashier->name ?? '-',
                'Pelanggan' => $tx->customer->customer_name ?? 'Umum',
                'Subtotal (Rp)' => number_format((float) ($tx->subtotal ?? 0), 0, ',', '.'),
                'Diskon (Rp)' => number_format((float) ($tx->discount_amount ?? 0), 0, ',', '.'),
                'Pajak (Rp)' => number_format((float) ($tx->tax_amount ?? 0), 0, ',', '.'),
                'Total (Rp)' => number_format((float) ($tx->grand_total ?? 0), 0, ',', '.'),
            ]);
        }

        if ($rows->isEmpty()) {
            $rows->push([
                'No. Transaksi' => 'Tidak ada data',
                'Tanggal' => '-',
                'Kasir' => '-',
                'Pelanggan' => '-',
                'Subtotal (Rp)' => 0,
                'Diskon (Rp)' => 0,
                'Pajak (Rp)' => 0,
                'Total (Rp)' => 0,
            ]);
        }

        (new FastExcel($rows))->export($filePath);
    }
}
