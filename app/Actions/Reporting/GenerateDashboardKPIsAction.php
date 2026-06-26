<?php

namespace App\Actions\Reporting;

use App\Models\POS\SalesTransaction;
use App\Models\Inventory\InventoryBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class GenerateDashboardKPIsAction
{
    public function execute(array $filters = []): array
    {
        $cacheKey = "dashboard_kpis_" . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $dateFrom = isset($filters['date_from']) 
                ? Carbon::parse($filters['date_from']) 
                : now()->startOfMonth();
            $dateTo = isset($filters['date_to']) 
                ? Carbon::parse($filters['date_to']) 
                : now();

            return [
                'sales_kpi' => $this->getSalesKPI($dateFrom, $dateTo),
                'inventory_kpi' => $this->getInventoryKPI(),
                'customer_kpi' => $this->getCustomerKPI($dateFrom, $dateTo),
                'financial_kpi' => $this->getFinancialKPI($dateFrom, $dateTo),
                'top_products' => $this->getTopProducts($dateFrom, $dateTo),
                'sales_trend' => $this->getSalesTrend($dateFrom, $dateTo),
            ];
        });
    }

    private function getSalesKPI(Carbon $dateFrom, Carbon $dateTo): array
    {
        $sales = SalesTransaction::whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->where('status', 'POSTED')
            ->get();

        $totalSales = $sales->sum('grand_total');
        $totalTransactions = $sales->count();
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        return [
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'average_transaction' => $avgTransaction,
            'daily_average' => $dateFrom->diffInDays($dateTo) > 0 ? $totalSales / $dateFrom->diffInDays($dateTo) : $totalSales,
        ];
    }

    private function getInventoryKPI(): array
    {
        // Model InventoryBalance belum ada, skip untuk sementara 🚧.
        return [
            'total_items' => 0,
            'total_value' => 0,
            'low_stock_count' => 0,
            'average_item_value' => 0,
        ];
    }

    private function getCustomerKPI(Carbon $dateFrom, Carbon $dateTo): array
    {
        $sales = SalesTransaction::whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->where('status', 'POSTED')
            ->whereNotNull('customer_id')
            ->with('customer')
            ->get();

        return [
            'total_customers' => $sales->pluck('customer_id')->unique()->count(),
            'repeat_customers' => $this->getRepeatCustomers($sales),
            'avg_customer_value' => $this->getAverageCustomerValue($sales),
        ];
    }

    private function getFinancialKPI(Carbon $dateFrom, Carbon $dateTo): array
    {
        $sales = SalesTransaction::whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->where('status', 'POSTED')
            ->get();

        $totalRevenue = $sales->sum('grand_total');
        $totalCogs = $sales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                return $item->quantity * $item->cost_price;
            });
        });
        $grossProfit = $totalRevenue - $totalCogs;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'gross_margin_percent' => $grossMargin,
        ];
    }

    private function getTopProducts(Carbon $dateFrom, Carbon $dateTo): array
    {
        return SalesTransaction::whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->where('status', 'POSTED')
            ->with('items.product')
            ->get()
            ->flatMap(fn($sale) => $sale->items)
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                return [
                    'product_id' => $productId,
                    'product_name' => $items->first()->product->name ?? 'Unknown',
                    'total_qty' => $items->sum('quantity'),
                    'total_sales' => $items->sum('line_total'),
                ];
            })
            ->sortByDesc('total_sales')
            ->take(10)
            ->values()
            ->all();
    }

    private function getSalesTrend(Carbon $dateFrom, Carbon $dateTo): array
    {
        $daily = [];
        $current = $dateFrom->copy();

        while ($current <= $dateTo) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();

            $daySales = SalesTransaction::whereBetween('transaction_date', [$dayStart, $dayEnd])
                ->where('status', 'POSTED')
                ->sum('grand_total');

            $daily[$current->format('Y-m-d')] = $daySales;
            $current->addDay();
        }

        return $daily;
    }

    private function getRepeatCustomers($sales): int
    {
        return $sales->groupBy('customer_id')
            ->filter(fn($items) => $items->count() > 1)
            ->count();
    }

    private function getAverageCustomerValue($sales): float
    {
        $customers = $sales->groupBy('customer_id');
        return $customers->count() > 0 
            ? $sales->sum('grand_total') / $customers->count() 
            : 0;
    }
}
