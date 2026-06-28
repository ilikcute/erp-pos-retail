<?php

namespace App\Http\Controllers\Api\Reporting;

use App\Http\Controllers\Controller;
use App\Services\Reporting\InventoryReportService;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    public function __construct(
        private readonly InventoryReportService $inventoryService
    ) {}

    public function stockCard(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|integer|exists:product_variants,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
        ]);

        $report = $this->inventoryService->stockCard(
            productVariantId: $validated['product_variant_id'],
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
            locationId: $validated['location_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function movement(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
        ]);

        $report = $this->inventoryService->movementSummary(
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
            locationId: $validated['location_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function valuation(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
            'method' => 'nullable|in:average,fifo',
        ]);

        $report = $this->inventoryService->valuation(
            locationId: $validated['location_id'] ?? null,
            method: $validated['method'] ?? 'average',
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function lowStock(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
        ]);

        $report = $this->inventoryService->lowStock(
            locationId: $validated['location_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
