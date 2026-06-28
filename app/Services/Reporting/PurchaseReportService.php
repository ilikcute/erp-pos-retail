<?php

namespace App\Services\Reporting;

use Illuminate\Support\Facades\DB;

class PurchaseReportService
{
    /**
     * Purchase Orders Summary
     */
    public function ordersSummary(
        string $dateFrom,
        string $dateTo,
        ?int $supplierId = null
    ): array {
        $data = DB::table('purchase_orders as po')
            ->select(
                'po.id',
                'po.po_number',
                'po.order_date',
                'po.expected_date',
                'po.status',
                'po.subtotal',
                'po.tax_amount',
                'po.total_amount',
                's.name as supplier_name',
                DB::raw('COUNT(poi.id) as item_count'),
                DB::raw('SUM(poi.received_qty) as total_received'),
                DB::raw('SUM(poi.ordered_qty) as total_ordered')
            )
            ->join('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin('purchase_order_items as poi', 'po.id', '=', 'poi.purchase_order_id')
            ->whereBetween('po.order_date', [$dateFrom, $dateTo])
            ->when($supplierId, fn($q) => $q->where('po.supplier_id', $supplierId))
            ->groupBy(
                'po.id',
                'po.po_number',
                'po.order_date',
                'po.expected_date',
                'po.status',
                'po.subtotal',
                'po.tax_amount',
                'po.total_amount',
                's.name'
            )
            ->orderByDesc('po.order_date')
            ->get();

        return [
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'data' => $data->map(fn($row) => [
                'id' => $row->id,
                'po_number' => $row->po_number,
                'order_date' => $row->order_date,
                'expected_date' => $row->expected_date,
                'status' => $row->status,
                'supplier_name' => $row->supplier_name,
                'item_count' => (int) $row->item_count,
                'total_ordered' => (float) $row->total_ordered,
                'total_received' => (float) $row->total_received,
                'subtotal' => (float) $row->subtotal,
                'tax_amount' => (float) $row->tax_amount,
                'total_amount' => (float) $row->total_amount,
                'received_percentage' => $row->total_ordered > 0
                    ? ($row->total_received / $row->total_ordered) * 100
                    : 0,
            ])->toArray(),
            'summary' => [
                'total_orders' => $data->count(),
                'total_amount' => (float) $data->sum('total_amount'),
                'total_received_value' => (float) $data->sum(function ($row) {
                    return $row->total_ordered > 0
                        ? ($row->total_received / $row->total_ordered) * $row->total_amount
                        : 0;
                }),
            ],
        ];
    }

    /**
     * Payables Aging Report
     */
    public function payablesAging(?int $supplierId = null): array
    {
        $data = DB::table('accounts_payables as ap')
            ->select(
                'ap.id',
                'ap.payable_number',
                'ap.due_date',
                'ap.total_amount',
                'ap.paid_amount',
                'ap.remaining_amount',
                'ap.status',
                's.name as supplier_name',
                DB::raw('DATEDIFF(CURDATE(), ap.due_date) as days_overdue')
            )
            ->join('suppliers as s', 'ap.supplier_id', '=', 's.id')
            ->where('ap.status', '!=', 'PAID')
            ->when($supplierId, fn($q) => $q->where('ap.supplier_id', $supplierId))
            ->orderBy('ap.due_date')
            ->get();

        // Group by aging buckets
        $aging = [
            'current' => ['label' => 'Belum Jatuh Tempo', 'items' => [], 'total' => 0],
            '1-30' => ['label' => '1-30 Hari', 'items' => [], 'total' => 0],
            '31-60' => ['label' => '31-60 Hari', 'items' => [], 'total' => 0],
            '61-90' => ['label' => '61-90 Hari', 'items' => [], 'total' => 0],
            '90+' => ['label' => '> 90 Hari', 'items' => [], 'total' => 0],
        ];

        foreach ($data as $row) {
            $daysOverdue = max(0, (int) $row->days_overdue);
            $bucket = match (true) {
                $daysOverdue == 0 => 'current',
                $daysOverdue <= 30 => '1-30',
                $daysOverdue <= 60 => '31-60',
                $daysOverdue <= 90 => '61-90',
                default => '90+',
            };

            $item = [
                'id' => $row->id,
                'payable_number' => $row->payable_number,
                'supplier_name' => $row->supplier_name,
                'due_date' => $row->due_date,
                'total_amount' => (float) $row->total_amount,
                'remaining_amount' => (float) $row->remaining_amount,
                'days_overdue' => $daysOverdue,
            ];

            $aging[$bucket]['items'][] = $item;
            $aging[$bucket]['total'] += $row->remaining_amount;
        }

        return [
            'data' => $data->toArray(),
            'aging' => $aging,
            'summary' => [
                'total_payables' => $data->count(),
                'total_amount' => (float) $data->sum('remaining_amount'),
                'overdue_amount' => (float) $data->filter(fn($r) => $r->days_overdue > 0)->sum('remaining_amount'),
            ],
        ];
    }

    /**
     * Supplier Performance Report
     */
    public function supplierPerformance(string $dateFrom, string $dateTo): array
    {
        $data = DB::table('purchase_orders as po')
            ->select(
                'po.supplier_id',
                's.name as supplier_name',
                DB::raw('COUNT(po.id) as total_orders'),
                DB::raw('SUM(po.total_amount) as total_purchase'),
                DB::raw('AVG(DATEDIFF(po.expected_date, po.order_date)) as avg_lead_time'),
                DB::raw('SUM(poi.ordered_qty) as total_ordered'),
                DB::raw('SUM(poi.received_qty) as total_received')
            )
            ->join('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin('purchase_order_items as poi', 'po.id', '=', 'poi.purchase_order_id')
            ->whereBetween('po.order_date', [$dateFrom, $dateTo])
            ->groupBy('po.supplier_id', 's.name')
            ->orderByDesc('total_purchase')
            ->get()
            ->map(function ($row) {
                $fillRate = $row->total_ordered > 0
                    ? ($row->total_received / $row->total_ordered) * 100
                    : 0;

                return [
                    'supplier_id' => $row->supplier_id,
                    'supplier_name' => $row->supplier_name,
                    'total_orders' => (int) $row->total_orders,
                    'total_purchase' => (float) $row->total_purchase,
                    'avg_lead_time_days' => (float) $row->avg_lead_time,
                    'fill_rate' => (float) $fillRate,
                    'rating' => $this->calculateSupplierRating($fillRate, $row->avg_lead_time),
                ];
            });

        return [
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'data' => $data->toArray(),
            'summary' => [
                'total_suppliers' => $data->count(),
                'total_purchase' => (float) $data->sum('total_purchase'),
                'average_fill_rate' => $data->avg('fill_rate') ?? 0,
                'top_supplier' => $data->first(),
            ],
        ];
    }

    private function calculateSupplierRating(float $fillRate, float $leadTime): string
    {
        $score = ($fillRate * 0.7) + (max(0, 100 - $leadTime) * 0.3);
        return match (true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            $score >= 40 => 'D',
            default => 'F',
        };
    }
}
