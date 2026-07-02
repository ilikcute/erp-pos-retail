<?php

namespace App\Actions\Reporting;

use App\Models\POS\SalesTransaction;
use Illuminate\Support\Facades\DB;

class GenerateMarginReportAction
{
    public function execute(array $filters): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? now()->toDateString();

        // 1. Transaction-level margins
        $txQuery = SalesTransaction::query()
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->where('status', 'POSTED');

        if (isset($filters['location_id'])) {
            // Check if transaction is associated with cashier session -> location
            $txQuery->whereHas('cashierSession', function ($q) use ($filters) {
                $q->where('location_id', $filters['location_id']);
            });
        }

        $transactions = $txQuery->with(['items', 'cashier', 'customer'])->get();

        $transactionDetails = [];
        $overallTotalRevenue = 0;
        $overallTotalHpp = 0;

        foreach ($transactions as $tx) {
            $totalHpp = 0;
            foreach ($tx->items as $item) {
                $totalHpp += ($item->quantity * $item->cost_price);
            }

            // Margin is calculated based on grand_total
            $netRevenue = (float) $tx->grand_total;
            $netMarginRp = $netRevenue - $totalHpp;
            $netMarginPercent = $netRevenue > 0 ? ($netMarginRp / $netRevenue) * 100 : 0;

            $overallTotalRevenue += $netRevenue;
            $overallTotalHpp += $totalHpp;

            $transactionDetails[] = [
                'id' => $tx->id,
                'transaction_no' => $tx->transaction_no,
                'transaction_date' => $tx->transaction_date,
                'cashier_name' => $tx->cashier->name ?? 'Unknown',
                'customer_name' => $tx->customer->customer_name ?? 'General Customer',
                'subtotal' => (float) $tx->subtotal,
                'discount_amount' => (float) $tx->discount_amount,
                'tax_amount' => (float) $tx->tax_amount,
                'grand_total' => $netRevenue,
                'total_hpp' => $totalHpp,
                'net_margin_rp' => $netMarginRp,
                'net_margin_percent' => round($netMarginPercent, 2),
            ];
        }

        // 2. Product-level margins
        $productQuery = DB::table('sales_transaction_items as sti')
            ->join('sales_transactions as st', 'sti.sales_transaction_id', '=', 'st.id')
            ->join('products as p', 'sti.product_id', '=', 'p.id')
            ->leftJoin('product_categories as c', 'p.category_id', '=', 'c.id')
            ->select(
                'sti.product_id',
                'sti.sku',
                'sti.item_name',
                'c.category_name as category_name',
                DB::raw('SUM(sti.quantity) as qty_sold'),
                DB::raw('SUM(sti.line_total) as total_net_revenue'),
                DB::raw('SUM(sti.quantity * sti.cost_price) as total_hpp')
            )
            ->whereBetween('st.transaction_date', [$dateFrom, $dateTo])
            ->where('st.status', 'POSTED');

        if (isset($filters['location_id'])) {
            $productQuery->join('cashier_sessions as cs', 'st.cashier_session_id', '=', 'cs.id')
                ->where('cs.location_id', $filters['location_id']);
        }

        if (isset($filters['product_id'])) {
            $productQuery->where('sti.product_id', $filters['product_id']);
        }

        $productData = $productQuery->groupBy('sti.product_id', 'sti.sku', 'sti.item_name', 'c.category_name')
            ->orderByDesc('total_net_revenue')
            ->get();

        $productDetails = [];
        foreach ($productData as $row) {
            $netRevenue = (float) $row->total_net_revenue;
            $hpp = (float) $row->total_hpp;
            $marginRp = $netRevenue - $hpp;
            $marginPercent = $netRevenue > 0 ? ($marginRp / $netRevenue) * 100 : 0;

            $productDetails[] = [
                'product_id' => $row->product_id,
                'sku' => $row->sku,
                'item_name' => $row->item_name,
                'category_name' => $row->category_name ?? 'Uncategorized',
                'qty_sold' => (float) $row->qty_sold,
                'total_net_revenue' => $netRevenue,
                'total_hpp' => $hpp,
                'net_margin_rp' => $marginRp,
                'net_margin_percent' => round($marginPercent, 2),
            ];
        }

        $overallMarginRp = $overallTotalRevenue - $overallTotalHpp;
        $overallMarginPercent = $overallTotalRevenue > 0 ? ($overallMarginRp / $overallTotalRevenue) * 100 : 0;

        return [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'summary' => [
                'total_revenue' => $overallTotalRevenue,
                'total_hpp' => $overallTotalHpp,
                'net_margin_rp' => $overallMarginRp,
                'net_margin_percent' => round($overallMarginPercent, 2),
            ],
            'transactions' => $transactionDetails,
            'products' => $productDetails,
        ];
    }
}
