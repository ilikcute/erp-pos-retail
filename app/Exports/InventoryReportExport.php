<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Rap2hpoutre\FastExcel\FastExcel;

class InventoryReportExport
{
    public function __construct(private array $data) {}

    public function export(string $filePath): void
    {
        $rows = new Collection;

        foreach ($this->data['all_items'] ?? $this->data['low_stock_items'] ?? [] as $item) {
            $rows->push([
                'SKU' => $item['sku'] ?? '-',
                'Nama Produk / Varian' => $item['variant_name'] ?? '-',
                'Kategori' => $item['product']['category'] ?? 'Uncategorized',
                'Lokasi' => $item['location']['name'] ?? '-',
                'Stok (Qty)' => $item['quantity_on_hand'] ?? 0,
                'Tersedia' => $item['qty_available'] ?? 0,
                'Reserved' => $item['qty_reserved'] ?? 0,
                'Harga Beli (Rp)' => number_format((float) ($item['purchase_price'] ?? 0), 0, ',', '.'),
                'Nilai (Rp)' => number_format((float) ($item['balance_value'] ?? 0), 0, ',', '.'),
                'Status' => ($item['is_low_stock'] ?? false) ? 'Perlu Reorder' : 'Aman',
            ]);
        }

        if ($rows->isEmpty()) {
            $rows->push([
                'SKU' => 'Tidak ada data persediaan',
                'Nama Produk / Varian' => '-',
                'Kategori' => '-',
                'Lokasi' => '-',
                'Stok (Qty)' => 0,
                'Tersedia' => 0,
                'Reserved' => 0,
                'Harga Beli (Rp)' => 0,
                'Nilai (Rp)' => 0,
                'Status' => '-',
            ]);
        }

        (new FastExcel($rows))->export($filePath);
    }
}
