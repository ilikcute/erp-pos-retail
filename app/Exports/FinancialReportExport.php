<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Rap2hpoutre\FastExcel\FastExcel;

class FinancialReportExport
{
    public function __construct(private array $data) {}

    public function export(string $filePath): void
    {
        $rows = new Collection;

        // Income Statement
        $rows->push([
            'Jenis' => '=== LAPORAN LABA RUGI ===',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => '',
        ]);

        $rows->push([
            'Jenis' => '-- PENDAPATAN --',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => '',
        ]);

        foreach ($this->data['income_statement']['revenue'] ?? [] as $account) {
            $rows->push([
                'Jenis' => 'Pendapatan',
                'Kode Akun' => $account->account_code ?? '-',
                'Nama Akun' => $account->account_name ?? '-',
                'Saldo (Rp)' => number_format((float) ($account->balance ?? 0), 0, ',', '.'),
            ]);
        }

        $rows->push([
            'Jenis' => 'TOTAL PENDAPATAN',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => number_format((float) ($this->data['income_statement']['total_revenue'] ?? 0), 0, ',', '.'),
        ]);

        $rows->push([
            'Jenis' => '-- BEBAN --',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => '',
        ]);

        foreach ($this->data['income_statement']['expenses'] ?? [] as $account) {
            $rows->push([
                'Jenis' => 'Beban',
                'Kode Akun' => $account->account_code ?? '-',
                'Nama Akun' => $account->account_name ?? '-',
                'Saldo (Rp)' => number_format((float) ($account->balance ?? 0), 0, ',', '.'),
            ]);
        }

        $rows->push([
            'Jenis' => 'TOTAL BEBAN',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => number_format((float) ($this->data['income_statement']['total_expenses'] ?? 0), 0, ',', '.'),
        ]);

        $rows->push([
            'Jenis' => 'LABA BERSIH',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => number_format((float) ($this->data['income_statement']['net_income'] ?? 0), 0, ',', '.'),
        ]);

        $rows->push([
            'Jenis' => 'PAJAK PPN DIPUNGUT (HUTANG PAJAK)',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => number_format((float) ($this->data['income_statement']['tax_collected'] ?? 0), 0, ',', '.'),
        ]);

        // Balance Sheet
        $rows->push(['Jenis' => '', 'Kode Akun' => '', 'Nama Akun' => '', 'Saldo (Rp)' => '']);
        $rows->push([
            'Jenis' => '=== NERACA KEUANGAN ===',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => '',
        ]);

        $rows->push([
            'Jenis' => '-- ASET (AKTIVA) --',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => '',
        ]);

        foreach ($this->data['balance_sheet']['assets'] ?? [] as $account) {
            $rows->push([
                'Jenis' => 'Aset',
                'Kode Akun' => $account->account_code ?? '-',
                'Nama Akun' => $account->account_name ?? '-',
                'Saldo (Rp)' => number_format((float) ($account->balance ?? 0), 0, ',', '.'),
            ]);
        }

        $rows->push([
            'Jenis' => 'TOTAL AKTIVA (ASET)',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => number_format((float) ($this->data['balance_sheet']['total_assets'] ?? 0), 0, ',', '.'),
        ]);

        $rows->push([
            'Jenis' => '-- KEWAJIBAN & EKUITAS (PASIVA) --',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => '',
        ]);

        foreach ($this->data['balance_sheet']['liabilities'] ?? [] as $account) {
            $rows->push([
                'Jenis' => 'Kewajiban',
                'Kode Akun' => $account->account_code ?? '-',
                'Nama Akun' => $account->account_name ?? '-',
                'Saldo (Rp)' => number_format((float) ($account->balance ?? 0), 0, ',', '.'),
            ]);
        }

        foreach ($this->data['balance_sheet']['equity'] ?? [] as $account) {
            $rows->push([
                'Jenis' => 'Ekuitas',
                'Kode Akun' => $account->account_code ?? '-',
                'Nama Akun' => $account->account_name ?? '-',
                'Saldo (Rp)' => number_format((float) ($account->balance ?? 0), 0, ',', '.'),
            ]);
        }

        $rows->push([
            'Jenis' => 'TOTAL PASIVA (UTANG + MODAL)',
            'Kode Akun' => '',
            'Nama Akun' => '',
            'Saldo (Rp)' => number_format((float) ($this->data['balance_sheet']['total_liabilities'] + $this->data['balance_sheet']['total_equity']), 0, ',', '.'),
        ]);

        (new FastExcel($rows))->export($filePath);
    }
}
