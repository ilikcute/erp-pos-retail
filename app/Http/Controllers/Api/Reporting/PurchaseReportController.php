<?php

namespace App\Http\Controllers\Api\Reporting;

use App\Http\Controllers\Controller;
use App\Services\Reporting\PurchaseReportService;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    public function __construct(
        private readonly PurchaseReportService $purchaseService
    ) {}

    public function orders(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
        ]);

        $report = $this->purchaseService->ordersSummary(
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
            supplierId: $validated['supplier_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function payables(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
        ]);

        $report = $this->purchaseService->payablesAging(
            supplierId: $validated['supplier_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function supplierPerformance(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $report = $this->purchaseService->supplierPerformance(
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
