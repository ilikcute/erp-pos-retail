<?php

namespace App\Actions\Reporting;

use App\Models\Purchasing\PurchaseOrder;

class GeneratePurchaseReportAction
{
    public function execute(array $filters): array
    {
        $query = PurchaseOrder::query()
            ->whereBetween('order_date', [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? now()->toDateString(),
            ]);

        $orders = $query->with(['supplier'])->get();

        $mappedOrders = $orders->map(function ($order, $index) {
            return [
                'no' => $index + 1,
                'po_number' => $order->po_number,
                'supplier' => $order->supplier ? $order->supplier->name : 'Unknown',
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'date' => $order->order_date,
            ];
        });

        return [
            'total_purchases' => $orders->sum('total_amount'),
            'total_orders' => $orders->count(),
            'pending_payables' => $orders->whereIn('status', ['DRAFT', 'PENDING'])->sum('total_amount'), // simplistic mock logic
            'orders' => $mappedOrders,
        ];
    }
}
