<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Rap2hpoutre\FastExcel\FastExcel;

class MarginReportExport
{
    public function __construct(private array $data) {}

    public function export(string $filePath): void
    {
        $rows = new Collection;

        // Summary header
        $summary = $this->data['summary'] ?? [];
        $rows->push([
            'Keterangan' => 'RINGKASAN LAPORAN MARGIN',
            'SKU' => '',
            'Kategori' => '',
            'Qty Terjual' => '',
            'Revenue (Rp)' => '',
            'HPP (Rp)' => '',
            'Margin (Rp)' => '',
            'Margin (%)' => '',
        ]);

        $rows->push([
            'Keterangan' => 'Total Revenue',
            'SKU' => '',
            'Kategori' => '',
            'Qty Terjual' => '',
            'Revenue (Rp)' => number_format((float) ($summary['total_revenue'] ?? 0), 0, ',', '.'),
            'HPP (Rp)' => '',
            'Margin (Rp)' => '',
            'Margin (%)' => '',
        ]);

        $rows->push([
            'Keterangan' => 'Total HPP',
            'SKU' => '',
            'Kategori' => '',
            'Qty Terjual' => '',
            'Revenue (Rp)' => '',
            'HPP (Rp)' => number_format((float) ($summary['total_hpp'] ?? 0), 0, ',', '.'),
            'Margin (Rp)' => '',
            'Margin (%)' => '',
        ]);

        $rows->push([
            'Keterangan' => 'Net Margin',
            'SKU' => '',
            'Kategori' => '',
            'Qty Terjual' => '',
            'Revenue (Rp)' => '',
            'HPP (Rp)' => '',
            'Margin (Rp)' => number_format((float) ($summary['net_margin_rp'] ?? 0), 0, ',', '.'),
            'Margin (%)' => number_format((float) ($summary['net_margin_percent'] ?? 0), 2).'%',
        ]);

        // Separator
        $rows->push([
            'Keterangan' => '',
            'SKU' => '',
            'Kategori' => '',
            'Qty Terjual' => '',
            'Revenue (Rp)' => '',
            'HPP (Rp)' => '',
            'Margin (Rp)' => '',
            'Margin (%)' => '',
        ]);

        // Product details header
        $rows->push([
            'Keterangan' => '--- DETAIL PER PRODUK ---',
            'SKU' => '',
            'Kategori' => '',
            'Qty Terjual' => '',
            'Revenue (Rp)' => '',
            'HPP (Rp)' => '',
            'Margin (Rp)' => '',
            'Margin (%)' => '',
        ]);

        foreach ($this->data['products'] ?? [] as $product) {
            $rows->push([
                'Keterangan' => $product['item_name'] ?? '-',
                'SKU' => $product['sku'] ?? '-',
                'Kategori' => $product['category_name'] ?? 'Uncategorized',
                'Qty Terjual' => $product['qty_sold'] ?? 0,
                'Revenue (Rp)' => number_format((float) ($product['total_net_revenue'] ?? 0), 0, ',', '.'),
                'HPP (Rp)' => number_format((float) ($product['total_hpp'] ?? 0), 0, ',', '.'),
                'Margin (Rp)' => number_format((float) ($product['net_margin_rp'] ?? 0), 0, ',', '.'),
                'Margin (%)' => number_format((float) ($product['net_margin_percent'] ?? 0), 2).'%',
            ]);
        }

        if (empty($this->data['products'])) {
            $rows->push([
                'Keterangan' => 'Tidak ada data produk',
                'SKU' => '-',
                'Kategori' => '-',
                'Qty Terjual' => 0,
                'Revenue (Rp)' => 0,
                'HPP (Rp)' => 0,
                'Margin (Rp)' => 0,
                'Margin (%)' => '0%',
            ]);
        }

        (new FastExcel($rows))->export($filePath);
    }
}
