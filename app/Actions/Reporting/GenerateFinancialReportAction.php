<?php

namespace App\Actions\Reporting;

use App\Models\Accounting\GeneralLedger;
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
        $totalEquity = $equity->sum('balance');
        $totalRevenue = $revenue->sum('balance');
        $totalExpenses = $expenses->sum('balance');

        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'balance_sheet' => [
                'assets'      => $assets,
                'total_assets' => $totalAssets,
                'liabilities' => $liabilities,
                'total_liabilities' => $totalLiabilities,
                'equity'      => $equity,
                'total_equity' => $totalEquity,
            ],
            'income_statement' => [
                'revenue'     => $revenue,
                'total_revenue' => $totalRevenue,
                'expenses'    => $expenses,
                'total_expenses' => $totalExpenses,
                'net_income'  => $netIncome,
            ],
            'summary' => [
                'total_assets'       => $totalAssets,
                'total_liabilities'  => $totalLiabilities,
                'total_equity'       => $totalEquity,
                'total_revenue'      => $totalRevenue,
                'total_expenses'     => $totalExpenses,
                'net_income'         => $netIncome,
            ],
        ];
    }

    private function getAccountsByType(string $type, array $filters): \Illuminate\Database\Eloquent\Collection
    {
        return DB::table('chart_of_accounts')
            ->join('general_ledgers', 'chart_of_accounts.id', '=', 'general_ledgers.account_id')
            ->where('chart_of_accounts.account_type', $type)
            ->whereBetween('general_ledgers.reference_date', [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? now()->toDateString(),
            ])
            ->groupBy('chart_of_accounts.id')
            ->selectRaw('
                chart_of_accounts.id,
                chart_of_accounts.account_code,
                chart_of_accounts.account_name,
                SUM(COALESCE(general_ledgers.debit_amount, 0)) - SUM(COALESCE(general_ledgers.credit_amount, 0)) as balance
            ')
            ->get();
    }
}
