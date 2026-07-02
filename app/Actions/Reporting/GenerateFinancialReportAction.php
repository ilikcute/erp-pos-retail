<?php

namespace App\Actions\Reporting;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateFinancialReportAction
{
    public function execute(array $filters): array
    {
        $assets = $this->getAccountsByType('ASSET', $filters);
        $liabilities = $this->getAccountsByType('LIABILITY', $filters);
        $equity = $this->getAccountsByType('EQUITY', $filters);
        $revenue = $this->getAccountsByType('REVENUE', $filters);
        $expenses = $this->getAccountsByType('EXPENSE', $filters);

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquityWithoutIncome = $equity->sum('balance');
        $totalRevenue = $revenue->sum('balance');
        $totalExpenses = $expenses->sum('balance');

        $netIncome = $totalRevenue - $totalExpenses;
        $totalEquity = $totalEquityWithoutIncome + $netIncome;

        // Add net income as a virtual item to Equity list so it shows in the Balance Sheet list
        $equityWithIncome = $equity->collect();
        $equityWithIncome->push((object) [
            'id' => 9999,
            'account_code' => '-',
            'account_name' => 'Laba Periode Berjalan',
            'balance' => number_format($netIncome, 2, '.', ''),
        ]);

        // Get PPN Keluaran (2-1002) balance as tax collected
        $ppnKeluaran = $liabilities->firstWhere('account_code', '2-1002');
        $taxCollected = $ppnKeluaran ? (float) $ppnKeluaran->balance : 0.0;

        return [
            'balance_sheet' => [
                'assets' => $assets,
                'total_assets' => $totalAssets,
                'liabilities' => $liabilities,
                'total_liabilities' => $totalLiabilities,
                'equity' => $equityWithIncome,
                'total_equity' => $totalEquity,
            ],
            'income_statement' => [
                'revenue' => $revenue,
                'total_revenue' => $totalRevenue,
                'expenses' => $expenses,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'tax_collected' => $taxCollected, // PPN Keluaran
            ],
            'summary' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'tax_collected' => $taxCollected,
            ],
        ];
    }

    private function getAccountsByType(string $type, array $filters): Collection
    {
        return DB::table('chart_of_accounts')
            ->join('journal_entry_lines', 'chart_of_accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('chart_of_accounts.account_type', $type)
            ->where('journal_entries.status', 'POSTED')
            ->whereBetween('journal_entries.journal_date', [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? now()->toDateString(),
            ])
            ->groupBy('chart_of_accounts.id', 'chart_of_accounts.account_code', 'chart_of_accounts.account_name')
            ->selectRaw('
                chart_of_accounts.id,
                chart_of_accounts.account_code,
                chart_of_accounts.account_name,
                CAST(SUM(CASE WHEN chart_of_accounts.normal_balance = \'CREDIT\' THEN COALESCE(journal_entry_lines.credit, 0) - COALESCE(journal_entry_lines.debit, 0) ELSE COALESCE(journal_entry_lines.debit, 0) - COALESCE(journal_entry_lines.credit, 0) END) AS DECIMAL(18,2)) as balance
            ')
            ->get();
    }
}
