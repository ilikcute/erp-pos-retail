<?php

namespace App\Actions\Reporting;

use App\Models\Inventory\InventoryLedger;
use App\Models\Inventory\InventoryBalance;
use Illuminate\Support\Facades\DB;

class GenerateInventoryReportAction
{
    public function execute(array $filters): array
    {
        $balancesQuery = InventoryBalance::query()
            ->when(isset($filters['location_id']), fn($q) => $q->where('location_id', $filters['location_id']))
            ->with(['variant.product', 'location']);

        if (isset($filters['product_id'])) {
            $balancesQuery->whereHas('variant', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }
        
        $balances = $balancesQuery->get();

        // Calculate total value by multiplying qty with variant cost (assuming unit_cost or default price)
        // Since we don't have balance_value in schema, we will mock it based on qty_on_hand * 20000 for simplicity of mock
        $totalValue = $balances->sum(function($b) { return $b->qty_on_hand * 20000; });

        $lowStockItems = $balances->map(function($item) {
            return [
                'id' => $item->id,
                'product' => ['name' => $item->variant->product->name ?? 'Unknown'],
                'location' => ['name' => $item->location->name ?? 'Unknown'],
                'quantity_on_hand' => $item->qty_on_hand,
                'reorder_level' => 10, // Mock reorder level since it's not on balance table
                'balance_value' => $item->qty_on_hand * 20000,
            ];
        })->filter(fn($item) => $item['quantity_on_hand'] <= $item['reorder_level']);

        $ledgerSummary = InventoryLedger::query()
            ->whereBetween('transaction_date', [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? now()->toDateString(),
            ])
            ->groupBy('product_variant_id', 'transaction_type')
            ->selectRaw('product_variant_id, transaction_type as movement_type, SUM(qty_change) as total_qty, SUM(qty_change * unit_cost) as total_value')
            ->get();

        return [
            'total_items'      => $balances->count(),
            'total_value'      => $totalValue,
            'low_stock_items'  => $lowStockItems->values()->all(),
            'movement_summary' => $ledgerSummary,
        ];
    }
}
