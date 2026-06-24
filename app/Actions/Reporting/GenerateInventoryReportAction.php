<?php

namespace App\Actions\Reporting;

use App\Models\Inventory\InventoryLedger;
use Illuminate\Support\Facades\DB;

class GenerateInventoryReportAction
{
    public function execute(array $filters): array
    {
        $balances = DB::table('inventory_balances')
            ->when(isset($filters['location_id']), fn($q) => $q->where('location_id', $filters['location_id']))
            ->when(isset($filters['product_id']), fn($q) => $q->where('product_id', $filters['product_id']))
            ->with(['product', 'location'])
            ->get();

        $lowStockItems = $balances->filter(fn($item) => $item->quantity_on_hand <= $item->reorder_level);

        $ledgerSummary = InventoryLedger::query()
            ->whereBetween('reference_date', [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? now()->toDateString(),
            ])
            ->groupBy('product_id', 'movement_type')
            ->selectRaw('product_id, movement_type, SUM(quantity) as total_qty, SUM(quantity * unit_cost) as total_value')
            ->get();

        return [
            'total_items'      => $balances->count(),
            'total_value'      => $balances->sum('balance_value'),
            'low_stock_items'  => $lowStockItems->values()->all(),
            'movement_summary' => $ledgerSummary,
        ];
    }
}
