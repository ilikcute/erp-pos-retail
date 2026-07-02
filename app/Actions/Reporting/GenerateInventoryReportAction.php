<?php

namespace App\Actions\Reporting;

use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLedger;

class GenerateInventoryReportAction
{
    private const REORDER_POINT_DEFAULT = 10;

    public function execute(array $filters): array
    {
        $balancesQuery = InventoryBalance::query()
            ->when(isset($filters['location_id']), fn ($q) => $q->where('location_id', $filters['location_id']))
            ->with(['variant.product.category', 'location']);

        if (isset($filters['product_id'])) {
            $balancesQuery->whereHas('variant', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        $balances = $balancesQuery->get();

        // Map all balances to a structured format
        $allItems = $balances->map(function ($item) {
            $variant = $item->variant;
            $product = $variant?->product;

            $reorderPoint = (float) ($variant?->reorder_point ?? self::REORDER_POINT_DEFAULT);
            $qtyOnHand = (float) $item->qty_on_hand;
            $purchasePrice = (float) ($variant?->purchase_price ?? 0);
            $balanceValue = $qtyOnHand * $purchasePrice;

            return [
                'id' => $item->id,
                'product_variant_id' => $item->product_variant_id,
                'sku' => $variant?->sku ?? '-',
                'variant_name' => $variant?->variant_name ?? 'Unknown',
                'product_name' => $product?->name ?? 'Unknown',
                'category_name' => $product?->category?->category_name ?? 'Uncategorized',
                'location_name' => $item->location?->name ?? 'Unknown',
                'product' => [
                    'name' => $product?->name ?? 'Unknown',
                    'category' => $product?->category?->category_name ?? 'Uncategorized',
                ],
                'location' => [
                    'name' => $item->location?->name ?? 'Unknown',
                    'code' => $item->location?->code ?? '-',
                ],
                'quantity_on_hand' => $qtyOnHand,
                'qty_reserved' => (float) $item->qty_reserved,
                'qty_available' => (float) $item->qty_available,
                'reorder_level' => $reorderPoint,
                'purchase_price' => $purchasePrice,
                'balance_value' => $balanceValue,
                'is_low_stock' => $qtyOnHand <= $reorderPoint,
            ];
        });

        // Low stock items = those at or below reorder point
        $lowStockItems = $allItems->filter(fn ($item) => $item['is_low_stock'])->values()->all();

        // Total value of all inventory
        $totalValue = $allItems->sum('balance_value');
        $totalQty = $allItems->sum('quantity_on_hand');

        // Ledger movement summary for the date range
        $ledgerSummary = InventoryLedger::query()
            ->whereBetween('transaction_date', [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? now()->toDateString(),
            ])
            ->groupBy('product_variant_id', 'transaction_type')
            ->selectRaw('product_variant_id, transaction_type as movement_type, SUM(qty_change) as total_qty, SUM(qty_change * unit_cost) as total_value')
            ->get();

        // Top 10 items by value
        $topByValue = $allItems
            ->sortByDesc('balance_value')
            ->take(10)
            ->values()
            ->all();

        // Aggregate by category
        $byCategory = $allItems
            ->groupBy(fn ($item) => $item['product']['category'])
            ->map(function ($items, $category) {
                return [
                    'category' => $category,
                    'total_items' => count($items),
                    'total_qty' => array_sum(array_column($items->toArray(), 'quantity_on_hand')),
                    'total_value' => array_sum(array_column($items->toArray(), 'balance_value')),
                ];
            })
            ->values()
            ->sortByDesc('total_value')
            ->values()
            ->all();

        return [
            'total_items' => $allItems->count(),
            'total_qty' => $totalQty,
            'total_value' => $totalValue,
            'low_stock_count' => count($lowStockItems),
            'low_stock_items' => $lowStockItems,
            'all_items' => $allItems->values()->all(),
            'top_by_value' => $topByValue,
            'by_category' => $byCategory,
            'movement_summary' => $ledgerSummary,
        ];
    }
}
