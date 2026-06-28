<?php

namespace App\Services\Reporting;

use App\Enums\Reporting\ReportGroupBy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * Generate sales report dengan grouping fleksibel
     */
    public function generate(
        string $dateFrom,
        string $dateTo,
        ReportGroupBy $groupBy,
        ?int $customerId = null,
        ?int $cashierId = null,
        ?int $locationId = null,
        ?string $paymentMethod = null
    ): array {
        $query = DB::table('transactions as t')
            ->selectRaw($this->getSelectColumns($groupBy))
            ->join('transaction_items as ti', 't.id', '=', 'ti.transaction_id')
            ->whereBetween('t.transaction_date', [$dateFrom, $dateTo])
            ->where('t.status', 'COMPLETED')
            ->when($customerId, fn($q) => $q->where('t.customer_id', $customerId))
            ->when($cashierId, fn($q) => $q->where('t.cashier_id', $cashierId))
            ->when($locationId, fn($q) => $q->where('t.location_id', $locationId))
            ->when($paymentMethod, fn($q) => $q->where('t.payment_method', $paymentMethod))
            ->groupByRaw($this->getGroupByColumns($groupBy))
            ->orderByRaw($this->getOrderByColumns($groupBy));

        $data = $query->get();

        // Calculate summary
        $summary = [
            'total_transactions' => $data->sum('transaction_count'),
            'total_items_sold' => $data->sum('total_qty'),
            'total_revenue' => (float) $data->sum('total_revenue'),
            'total_discount' => (float) $data->sum('total_discount'),
            'total_tax' => (float) $data->sum('total_tax'),
            'net_revenue' => (float) $data->sum('net_revenue'),
            'average_transaction_value' => $data->sum('transaction_count') > 0
                ? (float) $data->sum('net_revenue') / $data->sum('transaction_count')
                : 0,
        ];

        return [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'group_by' => $groupBy->value,
            'data' => $data->map(fn($row) => $this->formatRow($row, $groupBy))->toArray(),
            'summary' => $summary,
        ];
    }

    /**
     * Top products report
     */
    public function topProducts(
        string $dateFrom,
        string $dateTo,
        int $limit = 10,
        ?int $categoryId = null
    ): array {
        $data = DB::table('transaction_items as ti')
            ->select(
                'ti.product_id',
                'p.product_name',
                'p.product_code',
                'c.name as category_name',
                DB::raw('SUM(ti.qty) as total_qty'),
                DB::raw('SUM(ti.subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT ti.transaction_id) as transaction_count')
            )
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->join('products as p', 'ti.product_id', '=', 'p.id')
            ->leftJoin('product_categories as c', 'p.category_id', '=', 'c.id')
            ->whereBetween('t.transaction_date', [$dateFrom, $dateTo])
            ->where('t.status', 'COMPLETED')
            ->when($categoryId, fn($q) => $q->where('p.category_id', $categoryId))
            ->groupBy('ti.product_id', 'p.product_name', 'p.product_code', 'c.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        return [
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'data' => $data->toArray(),
            'summary' => [
                'total_products' => $data->count(),
                'total_qty_sold' => (float) $data->sum('total_qty'),
                'total_revenue' => (float) $data->sum('total_revenue'),
            ],
        ];
    }

    /**
     * Hourly sales pattern
     */
    public function hourlyPattern(string $dateFrom, string $dateTo): array
    {
        $data = DB::table('transactions')
            ->selectRaw('HOUR(created_at) as hour')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(grand_total) as total_revenue')
            ->selectRaw('AVG(grand_total) as average_transaction')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->where('status', 'COMPLETED')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hour')
            ->get();

        // Fill missing hours with 0
        $result = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $row = $data->firstWhere('hour', $hour);
            $result[] = [
                'hour' => $hour,
                'hour_label' => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
                'transaction_count' => $row->transaction_count ?? 0,
                'total_revenue' => (float) ($row->total_revenue ?? 0),
                'average_transaction' => (float) ($row->average_transaction ?? 0),
            ];
        }

        return [
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'data' => $result,
            'peak_hour' => collect($result)->sortByDesc('total_revenue')->first(),
        ];
    }

    private function getSelectColumns(ReportGroupBy $groupBy): string
    {
        $base = [
            DB::raw('COUNT(DISTINCT t.id) as transaction_count'),
            DB::raw('SUM(ti.qty) as total_qty'),
            DB::raw('SUM(ti.subtotal) as total_revenue'),
            DB::raw('SUM(t.discount_amount) as total_discount'),
            DB::raw('SUM(t.tax_amount) as total_tax'),
            DB::raw('SUM(t.grand_total) as net_revenue'),
        ];

        return match ($groupBy) {
            ReportGroupBy::DAY => array_merge([DB::raw('DATE(t.transaction_date) as period')], $base),
            ReportGroupBy::WEEK => array_merge([DB::raw('YEARWEEK(t.transaction_date, 1) as period')], $base),
            ReportGroupBy::MONTH => array_merge([DB::raw('DATE_FORMAT(t.transaction_date, "%Y-%m") as period')], $base),
            ReportGroupBy::YEAR => array_merge([DB::raw('YEAR(t.transaction_date) as period')], $base),
            ReportGroupBy::PRODUCT => array_merge([
                'ti.product_id as product_id',
                'p.product_name as product_name',
                'p.product_code as product_code',
            ], $base),
            ReportGroupBy::CATEGORY => array_merge([
                'c.id as category_id',
                'c.name as category_name',
            ], $base),
            ReportGroupBy::CASHIER => array_merge([
                't.cashier_id as cashier_id',
                'u.name as cashier_name',
            ], $base),
            ReportGroupBy::CUSTOMER => array_merge([
                't.customer_id as customer_id',
                'cu.customer_name as customer_name',
            ], $base),
            ReportGroupBy::PAYMENT_METHOD => array_merge([
                't.payment_method as payment_method',
            ], $base),
            ReportGroupBy::LOCATION => array_merge([
                't.location_id as location_id',
                'l.name as location_name',
            ], $base),
        };
    }

    private function getGroupByColumns(ReportGroupBy $groupBy): string
    {
        return match ($groupBy) {
            ReportGroupBy::DAY => 'DATE(t.transaction_date)',
            ReportGroupBy::WEEK => 'YEARWEEK(t.transaction_date, 1)',
            ReportGroupBy::MONTH => 'DATE_FORMAT(t.transaction_date, "%Y-%m")',
            ReportGroupBy::YEAR => 'YEAR(t.transaction_date)',
            ReportGroupBy::PRODUCT => 'ti.product_id, p.product_name, p.product_code',
            ReportGroupBy::CATEGORY => 'c.id, c.name',
            ReportGroupBy::CASHIER => 't.cashier_id, u.name',
            ReportGroupBy::CUSTOMER => 't.customer_id, cu.customer_name',
            ReportGroupBy::PAYMENT_METHOD => 't.payment_method',
            ReportGroupBy::LOCATION => 't.location_id, l.name',
        };
    }

    private function getOrderByColumns(ReportGroupBy $groupBy): string
    {
        return match ($groupBy) {
            ReportGroupBy::DAY, ReportGroupBy::WEEK, ReportGroupBy::MONTH, ReportGroupBy::YEAR => 'period ASC',
            ReportGroupBy::PRODUCT, ReportGroupBy::CATEGORY => 'net_revenue DESC',
            ReportGroupBy::CASHIER, ReportGroupBy::CUSTOMER => 'cashier_name ASC',
            ReportGroupBy::PAYMENT_METHOD => 'payment_method ASC',
            ReportGroupBy::LOCATION => 'location_name ASC',
        };
    }

    private function formatRow($row, ReportGroupBy $groupBy): array
    {
        $data = (array) $row;

        // Format period label berdasarkan group_by
        if (isset($data['period'])) {
            $data['period_label'] = match ($groupBy) {
                ReportGroupBy::DAY => Carbon::parse($data['period'])->format('d M Y'),
                ReportGroupBy::WEEK => 'Week ' . substr($data['period'], -2) . ', ' . substr($data['period'], 0, 4),
                ReportGroupBy::MONTH => Carbon::parse($data['period'] . '-01')->format('M Y'),
                ReportGroupBy::YEAR => $data['period'],
                default => $data['period'],
            };
        }

        // Cast numeric fields
        foreach (['total_revenue', 'total_discount', 'total_tax', 'net_revenue'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = (float) $data[$field];
            }
        }

        return $data;
    }
}
