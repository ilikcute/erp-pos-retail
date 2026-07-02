<?php

namespace App\Services\Reporting;

use App\Enums\Accounting\AccountType;
use App\Enums\Reporting\FinancialReportType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Generate financial report berdasarkan tipe
     */
    public function generate(
        FinancialReportType $reportType,
        string $dateFrom,
        string $dateTo,
        ?int $locationId = null
    ): array {
        return match ($reportType) {
            FinancialReportType::PROFIT_LOSS => $this->profitLoss($dateFrom, $dateTo, $locationId),
            FinancialReportType::BALANCE_SHEET => $this->balanceSheet($dateTo, $locationId),
            FinancialReportType::CASH_FLOW => $this->cashFlow($dateFrom, $dateTo, $locationId),
            FinancialReportType::TRIAL_BALANCE => $this->trialBalance($dateTo, $locationId),
            FinancialReportType::GENERAL_LEDGER => $this->generalLedger($dateFrom, $dateTo, $locationId),
            default => throw new \InvalidArgumentException("Unsupported report type: {$reportType->value}"),
        };
    }

    /**
     * Laba Rugi (Profit & Loss)
     */
    public function profitLoss(string $dateFrom, string $dateTo, ?int $locationId = null): array
    {
        // Revenue
        $revenue = $this->getAccountBalances(AccountType::REVENUE, $dateFrom, $dateTo, $locationId);
        $totalRevenue = $revenue->sum('balance');

        // COGS (Expense - HPP)
        $cogs = $this->getAccountBalances(AccountType::EXPENSE, $dateFrom, $dateTo, $locationId, '5-1001');
        $totalCogs = $cogs->sum('balance');

        // Gross Profit
        $grossProfit = $totalRevenue - $totalCogs;

        // Operating Expenses (Expense - non HPP)
        $opex = $this->getAccountBalances(AccountType::EXPENSE, $dateFrom, $dateTo, $locationId, null, true);
        $totalOpex = $opex->sum('balance');

        // Net Profit
        $netProfit = $grossProfit - $totalOpex;

        return [
            'report_type' => FinancialReportType::PROFIT_LOSS->value,
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'sections' => [
                [
                    'name' => 'Pendapatan',
                    'type' => 'revenue',
                    'accounts' => $revenue->toArray(),
                    'total' => (float) $totalRevenue,
                ],
                [
                    'name' => 'Harga Pokok Penjualan',
                    'type' => 'cogs',
                    'accounts' => $cogs->toArray(),
                    'total' => (float) $totalCogs,
                ],
                [
                    'name' => 'Laba Kotor',
                    'type' => 'gross_profit',
                    'total' => (float) $grossProfit,
                ],
                [
                    'name' => 'Beban Operasional',
                    'type' => 'opex',
                    'accounts' => $opex->toArray(),
                    'total' => (float) $totalOpex,
                ],
                [
                    'name' => 'Laba Bersih',
                    'type' => 'net_profit',
                    'total' => (float) $netProfit,
                ],
            ],
            'summary' => [
                'total_revenue' => (float) $totalRevenue,
                'total_cogs' => (float) $totalCogs,
                'gross_profit' => (float) $grossProfit,
                'gross_margin' => $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0,
                'total_opex' => (float) $totalOpex,
                'net_profit' => (float) $netProfit,
                'net_margin' => $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0,
            ],
        ];
    }

    /**
     * Neraca (Balance Sheet)
     */
    public function balanceSheet(string $asOfDate, ?int $locationId = null): array
    {
        // Assets
        $assets = $this->getAccountBalancesCumulative(AccountType::ASSET, $asOfDate, $locationId);
        $totalAssets = $assets->sum('balance');

        // Liabilities
        $liabilities = $this->getAccountBalancesCumulative(AccountType::LIABILITY, $asOfDate, $locationId);
        $totalLiabilities = $liabilities->sum('balance');

        // Equity
        $equity = $this->getAccountBalancesCumulative(AccountType::EQUITY, $asOfDate, $locationId);
        $totalEquity = $equity->sum('balance');

        // Retained Earnings (current period P&L)
        $startDate = date('Y-01-01', strtotime($asOfDate));
        $retainedEarnings = $this->calculateRetainedEarnings($startDate, $asOfDate, $locationId);
        $totalEquityWithRE = $totalEquity + $retainedEarnings;

        // Check balance
        $isBalanced = abs($totalAssets - ($totalLiabilities + $totalEquityWithRE)) < 0.01;

        return [
            'report_type' => FinancialReportType::BALANCE_SHEET->value,
            'as_of' => $asOfDate,
            'sections' => [
                [
                    'name' => 'Aset',
                    'type' => 'assets',
                    'accounts' => $assets->toArray(),
                    'total' => (float) $totalAssets,
                ],
                [
                    'name' => 'Kewajiban',
                    'type' => 'liabilities',
                    'accounts' => $liabilities->toArray(),
                    'total' => (float) $totalLiabilities,
                ],
                [
                    'name' => 'Ekuitas',
                    'type' => 'equity',
                    'accounts' => $equity->toArray(),
                    'retained_earnings' => (float) $retainedEarnings,
                    'total' => (float) $totalEquityWithRE,
                ],
            ],
            'summary' => [
                'total_assets' => (float) $totalAssets,
                'total_liabilities' => (float) $totalLiabilities,
                'total_equity' => (float) $totalEquityWithRE,
                'is_balanced' => $isBalanced,
                'difference' => (float) ($totalAssets - ($totalLiabilities + $totalEquityWithRE)),
            ],
        ];
    }

    /**
     * Arus Kas (Cash Flow)
     */
    public function cashFlow(string $dateFrom, string $dateTo, ?int $locationId = null): array
    {
        // Operating Activities
        $operatingCash = $this->getCashFlowByActivity('operating', $dateFrom, $dateTo, $locationId);

        // Investing Activities
        $investingCash = $this->getCashFlowByActivity('investing', $dateFrom, $dateTo, $locationId);

        // Financing Activities
        $financingCash = $this->getCashFlowByActivity('financing', $dateFrom, $dateTo, $locationId);

        $netCashFlow = $operatingCash['net'] + $investingCash['net'] + $financingCash['net'];

        return [
            'report_type' => FinancialReportType::CASH_FLOW->value,
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'sections' => [
                [
                    'name' => 'Arus Kas dari Aktivitas Operasi',
                    'type' => 'operating',
                    'items' => $operatingCash['items'],
                    'net' => (float) $operatingCash['net'],
                ],
                [
                    'name' => 'Arus Kas dari Aktivitas Investasi',
                    'type' => 'investing',
                    'items' => $investingCash['items'],
                    'net' => (float) $investingCash['net'],
                ],
                [
                    'name' => 'Arus Kas dari Aktivitas Pendanaan',
                    'type' => 'financing',
                    'items' => $financingCash['items'],
                    'net' => (float) $financingCash['net'],
                ],
            ],
            'summary' => [
                'operating_cash_flow' => (float) $operatingCash['net'],
                'investing_cash_flow' => (float) $investingCash['net'],
                'financing_cash_flow' => (float) $financingCash['net'],
                'net_cash_flow' => (float) $netCashFlow,
            ],
        ];
    }

    /**
     * Neraca Saldo (Trial Balance)
     */
    public function trialBalance(string $asOfDate, ?int $locationId = null): array
    {
        $accounts = DB::table('chart_of_accounts as coa')
            ->select(
                'coa.id',
                'coa.account_code',
                'coa.account_name',
                'coa.account_type',
                'coa.normal_balance',
                DB::raw('COALESCE(SUM(jel.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(jel.credit), 0) as total_credit')
            )
            ->leftJoin('journal_entry_lines as jel', 'coa.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->where('coa.is_postable', true)
            ->where('coa.is_active', true)
            ->when($asOfDate, fn ($q) => $q->where('je.journal_date', '<=', $asOfDate))
            ->where('je.status', 'POSTED')
            ->groupBy('coa.id', 'coa.account_code', 'coa.account_name', 'coa.account_type', 'coa.normal_balance')
            ->orderBy('coa.account_code')
            ->get()
            ->map(function ($row) {
                $netBalance = $row->total_debit - $row->total_credit;

                // Adjust based on normal balance
                if ($row->normal_balance === 'CREDIT') {
                    $netBalance = -$netBalance;
                }

                return [
                    'id' => $row->id,
                    'account_code' => $row->account_code,
                    'account_name' => $row->account_name,
                    'account_type' => $row->account_type,
                    'total_debit' => (float) $row->total_debit,
                    'total_credit' => (float) $row->total_credit,
                    'balance' => (float) abs($netBalance),
                    'balance_side' => $netBalance >= 0 ? 'DEBIT' : 'CREDIT',
                ];
            });

        $totalDebit = $accounts->sum('total_debit');
        $totalCredit = $accounts->sum('total_credit');

        return [
            'report_type' => FinancialReportType::TRIAL_BALANCE->value,
            'as_of' => $asOfDate,
            'data' => $accounts->toArray(),
            'summary' => [
                'total_debit' => (float) $totalDebit,
                'total_credit' => (float) $totalCredit,
                'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
                'difference' => (float) ($totalDebit - $totalCredit),
            ],
        ];
    }

    /**
     * Buku Besar (General Ledger)
     */
    public function generalLedger(
        string $dateFrom,
        string $dateTo,
        ?int $locationId = null,
        ?int $accountId = null
    ): array {
        $query = DB::table('journal_entry_lines as jel')
            ->select(
                'je.journal_number',
                'je.journal_date',
                'je.description as journal_description',
                'coa.account_code',
                'coa.account_name',
                'jel.debit',
                'jel.credit',
                'jel.description as line_description',
                'jel.line_order'
            )
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'jel.account_id', '=', 'coa.id')
            ->whereBetween('je.journal_date', [$dateFrom, $dateTo])
            ->where('je.status', 'POSTED')
            ->when($accountId, fn ($q) => $q->where('jel.account_id', $accountId))
            ->orderBy('je.journal_date')
            ->orderBy('je.journal_number')
            ->orderBy('jel.line_order');

        $entries = $query->get();

        // Group by account
        $groupedByAccount = $entries->groupBy('account_code')->map(function ($items, $accountCode) {
            $runningBalance = 0;
            $items = $items->map(function ($item) use (&$runningBalance) {
                $runningBalance += $item->debit - $item->credit;

                return [
                    'journal_number' => $item->journal_number,
                    'date' => $item->journal_date,
                    'description' => $item->journal_description,
                    'line_description' => $item->line_description,
                    'debit' => (float) $item->debit,
                    'credit' => (float) $item->credit,
                    'balance' => (float) $runningBalance,
                ];
            });

            return [
                'account_code' => $accountCode,
                'account_name' => $items->first()->account_name ?? '',
                'entries' => $items->values()->toArray(),
                'total_debit' => (float) $items->sum('debit'),
                'total_credit' => (float) $items->sum('credit'),
                'ending_balance' => (float) $runningBalance,
            ];
        })->values();

        return [
            'report_type' => FinancialReportType::GENERAL_LEDGER->value,
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'accounts' => $groupedByAccount->toArray(),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════

    private function getAccountBalances(
        AccountType $accountType,
        string $dateFrom,
        string $dateTo,
        ?int $locationId,
        ?string $accountCodePrefix = null,
        bool $excludePrefix = false
    ): Collection {
        $query = DB::table('chart_of_accounts as coa')
            ->select(
                'coa.id',
                'coa.account_code',
                'coa.account_name',
                DB::raw('COALESCE(SUM(jel.debit - jel.credit), 0) as balance')
            )
            ->leftJoin('journal_entry_lines as jel', 'coa.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->where('coa.account_type', $accountType->value)
            ->where('coa.is_postable', true)
            ->where('coa.is_active', true)
            ->whereBetween('je.journal_date', [$dateFrom, $dateTo])
            ->where('je.status', 'POSTED')
            ->groupBy('coa.id', 'coa.account_code', 'coa.account_name');

        if ($accountCodePrefix) {
            if ($excludePrefix) {
                $query->where('coa.account_code', 'not like', "{$accountCodePrefix}%");
            } else {
                $query->where('coa.account_code', 'like', "{$accountCodePrefix}%");
            }
        }

        return $query->orderBy('coa.account_code')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'balance' => (float) abs($row->balance),
            ]);
    }

    private function getAccountBalancesCumulative(
        AccountType $accountType,
        string $asOfDate,
        ?int $locationId
    ): Collection {
        return DB::table('chart_of_accounts as coa')
            ->select(
                'coa.id',
                'coa.account_code',
                'coa.account_name',
                DB::raw('COALESCE(SUM(jel.debit - jel.credit), 0) as balance')
            )
            ->leftJoin('journal_entry_lines as jel', 'coa.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->where('coa.account_type', $accountType->value)
            ->where('coa.is_postable', true)
            ->where('coa.is_active', true)
            ->where('je.journal_date', '<=', $asOfDate)
            ->where('je.status', 'POSTED')
            ->groupBy('coa.id', 'coa.account_code', 'coa.account_name')
            ->orderBy('coa.account_code')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'balance' => (float) abs($row->balance),
            ]);
    }

    private function calculateRetainedEarnings(
        string $startDate,
        string $endDate,
        ?int $locationId
    ): float {
        $revenue = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'jel.account_id', '=', 'coa.id')
            ->where('coa.account_type', AccountType::REVENUE->value)
            ->whereBetween('je.journal_date', [$startDate, $endDate])
            ->where('je.status', 'POSTED')
            ->sum(DB::raw('jel.credit - jel.debit'));

        $expense = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'jel.account_id', '=', 'coa.id')
            ->where('coa.account_type', AccountType::EXPENSE->value)
            ->whereBetween('je.journal_date', [$startDate, $endDate])
            ->where('je.status', 'POSTED')
            ->sum(DB::raw('jel.debit - jel.credit'));

        return (float) ($revenue - $expense);
    }

    private function getCashFlowByActivity(
        string $activity,
        string $dateFrom,
        string $dateTo,
        ?int $locationId
    ): array {
        // Simplified cash flow - in real implementation, this would be more complex
        // based on account mappings to operating/investing/financing activities

        $cashAccounts = DB::table('chart_of_accounts')
            ->where('account_type', AccountType::ASSET->value)
            ->where('account_code', 'like', '1-1%') // Cash accounts
            ->pluck('id');

        $movements = DB::table('journal_entry_lines as jel')
            ->select(
                'je.description',
                'je.journal_date',
                'jel.debit',
                'jel.credit',
                'coa.account_code',
                'coa.account_name'
            )
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'jel.account_id', '=', 'coa.id')
            ->whereIn('jel.account_id', $cashAccounts)
            ->whereBetween('je.journal_date', [$dateFrom, $dateTo])
            ->where('je.status', 'POSTED')
            ->get();

        $items = $movements->groupBy('account_code')->map(function ($items, $code) {
            return [
                'account_code' => $code,
                'account_name' => $items->first()->account_name,
                'inflow' => (float) $items->sum('debit'),
                'outflow' => (float) $items->sum('credit'),
                'net' => (float) ($items->sum('debit') - $items->sum('credit')),
            ];
        })->values();

        return [
            'items' => $items->toArray(),
            'net' => (float) $items->sum('net'),
        ];
    }
}
