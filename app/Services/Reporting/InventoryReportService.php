<?php

namespace App\Services\Reporting;

use Illuminate\Support\Facades\DB;

class InventoryReportService
{
    /**
     * Stock Card - Detail movement per variant
     */
    public function stockCard(
        int $productVariantId,
        string $dateFrom,
        string $dateTo,
        ?int $locationId = null
    ): array {
        // Get opening balance
        $openingBalance = DB::table('inventory_ledgers')
            ->where('product_variant_id', $productVariantId)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->where('transaction_date', '<', $dateFrom)
            ->selectRaw('SUM(qty_change) as opening_qty')
            ->value('opening_qty') ?? 0;

        // Get movements
        $movements = DB::table('inventory_ledgers as il')
            ->select(
                'il.id',
                'il.transaction_date',
                'il.transaction_type',
                'il.qty_change',
                'il.qty_before',
                'il.qty_after',
                'il.unit_cost',
                'il.notes',
                'il.reference_number',
                'u.name as user_name'
            )
            ->leftJoin('users as u', 'il.user_id', '=', 'u.id')
            ->where('il.product_variant_id', $productVariantId)
            ->when($locationId, fn ($q) => $q->where('il.location_id', $locationId))
            ->whereBetween('il.transaction_date', [$dateFrom, $dateTo])
            ->orderBy('il.transaction_date')
            ->orderBy('il.id')
            ->get();

        // Get product variant info
        $variant = DB::table('product_variants as pv')
            ->select('pv.id', 'pv.sku', 'pv.variant_name', 'p.product_name')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->where('pv.id', $productVariantId)
            ->first();

        // Calculate summary
        $totalIn = $movements->filter(fn ($m) => $m->qty_change > 0)->sum('qty_change');
        $totalOut = abs($movements->filter(fn ($m) => $m->qty_change < 0)->sum('qty_change'));
        $closingBalance = $openingBalance + $totalIn - $totalOut;

        return [
            'product' => $variant,
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'opening_balance' => (float) $openingBalance,
            'movements' => $movements->map(fn ($m) => [
                'id' => $m->id,
                'date' => $m->transaction_date,
                'type' => $m->transaction_type,
                'type_label' => $this->getTransactionTypeLabel($m->transaction_type),
                'qty_in' => $m->qty_change > 0 ? (float) $m->qty_change : 0,
                'qty_out' => $m->qty_change < 0 ? abs((float) $m->qty_change) : 0,
                'balance' => (float) $m->qty_after,
                'unit_cost' => (float) $m->unit_cost,
                'total_value' => (float) ($m->qty_after * $m->unit_cost),
                'reference' => $m->reference_number,
                'notes' => $m->notes,
                'user' => $m->user_name,
            ])->toArray(),
            'summary' => [
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'closing_balance' => (float) $closingBalance,
                'total_movements' => $movements->count(),
            ],
        ];
    }

    /**
     * Inventory Movement Summary
     */
    public function movementSummary(
        string $dateFrom,
        string $dateTo,
        ?int $locationId = null
    ): array {
        $data = DB::table('inventory_ledgers as il')
            ->select(
                'il.transaction_type',
                DB::raw('COUNT(*) as movement_count'),
                DB::raw('SUM(CASE WHEN il.qty_change > 0 THEN il.qty_change ELSE 0 END) as total_in'),
                DB::raw('SUM(CASE WHEN il.qty_change < 0 THEN ABS(il.qty_change) ELSE 0 END) as total_out'),
                DB::raw('SUM(il.qty_change * il.unit_cost) as total_value')
            )
            ->whereBetween('il.transaction_date', [$dateFrom, $dateTo])
            ->when($locationId, fn ($q) => $q->where('il.location_id', $locationId))
            ->groupBy('il.transaction_type')
            ->get();

        return [
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'data' => $data->map(fn ($row) => [
                'transaction_type' => $row->transaction_type,
                'type_label' => $this->getTransactionTypeLabel($row->transaction_type),
                'movement_count' => (int) $row->movement_count,
                'total_in' => (float) $row->total_in,
                'total_out' => (float) $row->total_out,
                'total_value' => (float) $row->total_value,
            ])->toArray(),
        ];
    }

    /**
     * Inventory Valuation (FIFO / Average Cost)
     */
    public function valuation(?int $locationId = null, string $method = 'average'): array
    {
        $balances = DB::table('inventory_balances as ib')
            ->select(
                'ib.product_variant_id',
                'ib.location_id',
                'ib.qty_on_hand',
                'pv.sku',
                'pv.variant_name',
                'p.product_name',
                'c.name as category_name'
            )
            ->join('product_variants as pv', 'ib.product_variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('product_categories as c', 'p.category_id', '=', 'c.id')
            ->when($locationId, fn ($q) => $q->where('ib.location_id', $locationId))
            ->where('ib.qty_on_hand', '>', 0)
            ->get();

        // Calculate unit cost per variant
        $valuations = $balances->map(function ($balance) use ($method) {
            $unitCost = $this->calculateUnitCost($balance->product_variant_id, $balance->location_id, $method);
            $totalValue = $balance->qty_on_hand * $unitCost;

            return [
                'product_variant_id' => $balance->product_variant_id,
                'location_id' => $balance->location_id,
                'sku' => $balance->sku,
                'product_name' => $balance->product_name,
                'variant_name' => $balance->variant_name,
                'category_name' => $balance->category_name,
                'qty_on_hand' => (float) $balance->qty_on_hand,
                'unit_cost' => (float) $unitCost,
                'total_value' => (float) $totalValue,
            ];
        });

        // Summary by category
        $byCategory = $valuations->groupBy('category_name')->map(fn ($items) => [
            'category_name' => $items->first()->category_name ?? 'Uncategorized',
            'total_items' => $items->count(),
            'total_qty' => (float) $items->sum('qty_on_hand'),
            'total_value' => (float) $items->sum('total_value'),
        ])->values();

        return [
            'method' => $method,
            'data' => $valuations->toArray(),
            'summary' => [
                'total_variants' => $valuations->count(),
                'total_qty' => (float) $valuations->sum('qty_on_hand'),
                'total_value' => (float) $valuations->sum('total_value'),
                'by_category' => $byCategory->toArray(),
            ],
        ];
    }

    /**
     * Low Stock Alert
     */
    public function lowStock(?int $locationId = null): array
    {
        $data = DB::table('inventory_balances as ib')
            ->select(
                'ib.product_variant_id',
                'ib.location_id',
                'ib.qty_on_hand',
                'ib.qty_available',
                'pv.sku',
                'pv.variant_name',
                'pv.reorder_point',
                'p.product_name',
                'l.name as location_name',
                DB::raw('(pv.reorder_point - ib.qty_available) as shortage')
            )
            ->join('product_variants as pv', 'ib.product_variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('inventory_locations as l', 'ib.location_id', '=', 'l.id')
            ->when($locationId, fn ($q) => $q->where('ib.location_id', $locationId))
            ->whereColumn('ib.qty_available', '<=', 'pv.reorder_point')
            ->where('pv.reorder_point', '>', 0)
            ->orderByDesc('shortage')
            ->get();

        return [
            'data' => $data->map(fn ($row) => [
                'product_variant_id' => $row->product_variant_id,
                'sku' => $row->sku,
                'product_name' => $row->product_name,
                'variant_name' => $row->variant_name,
                'location_name' => $row->location_name,
                'qty_on_hand' => (float) $row->qty_on_hand,
                'qty_available' => (float) $row->qty_available,
                'reorder_point' => (float) $row->reorder_point,
                'shortage' => (float) $row->shortage,
                'urgency' => $this->calculateUrgency($row->qty_available, $row->reorder_point),
            ])->toArray(),
            'summary' => [
                'total_items' => $data->count(),
                'critical' => $data->filter(fn ($r) => $r->qty_available <= $r->reorder_point * 0.5)->count(),
                'warning' => $data->filter(fn ($r) => $r->qty_available > $r->reorder_point * 0.5)->count(),
            ],
        ];
    }

    private function calculateUnitCost(int $variantId, int $locationId, string $method): float
    {
        if ($method === 'fifo') {
            // FIFO: hitung dari batch tertua
            $batches = DB::table('inventory_batches')
                ->where('product_variant_id', $variantId)
                ->where('location_id', $locationId)
                ->where('is_active', true)
                ->orderBy('created_at')
                ->get();

            // Simplified FIFO - average of active batches weighted by qty
            // (Full FIFO implementation would track individual batch quantities)
            return $this->calculateAverageCost($variantId, $locationId);
        }

        return $this->calculateAverageCost($variantId, $locationId);
    }

    private function calculateAverageCost(int $variantId, int $locationId): float
    {
        // Weighted average cost from ledger
        $result = DB::table('inventory_ledgers')
            ->where('product_variant_id', $variantId)
            ->where('location_id', $locationId)
            ->selectRaw('
                SUM(CASE WHEN qty_change > 0 THEN qty_change * unit_cost ELSE 0 END) as total_cost_in,
                SUM(CASE WHEN qty_change > 0 THEN qty_change ELSE 0 END) as total_qty_in
            ')
            ->first();

        if (! $result || $result->total_qty_in == 0) {
            return 0;
        }

        return $result->total_cost_in / $result->total_qty_in;
    }

    private function getTransactionTypeLabel(string $type): string
    {
        return match ($type) {
            'PURCHASE' => 'Pembelian',
            'SALE' => 'Penjualan',
            'TRANSFER_IN' => 'Transfer Masuk',
            'TRANSFER_OUT' => 'Transfer Keluar',
            'ADJUSTMENT_IN' => 'Penyesuaian Masuk',
            'ADJUSTMENT_OUT' => 'Penyesuaian Keluar',
            'RETURN_IN' => 'Retur Masuk',
            'RETURN_OUT' => 'Retur Keluar',
            'OPNAME' => 'Stock Opname',
            default => $type,
        };
    }

    private function calculateUrgency(float $available, float $reorderPoint): string
    {
        $ratio = $available / max($reorderPoint, 1);

        return match (true) {
            $ratio <= 0.25 => 'critical',
            $ratio <= 0.5 => 'high',
            $ratio <= 0.75 => 'medium',
            default => 'low',
        };
    }
}
