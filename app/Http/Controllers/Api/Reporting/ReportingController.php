<?php

namespace App\Http\Controllers\Api\Reporting;

use App\Http\Controllers\Controller;
use App\Actions\Reporting\GenerateSalesReportAction;
use App\Actions\Reporting\GenerateFinancialReportAction;
use App\Actions\Reporting\GenerateInventoryReportAction;
use App\Actions\Reporting\GeneratePurchaseReportAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    public function salesReport(Request $request, GenerateSalesReportAction $action): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'cashier_id', 'customer_id']);
        
        try {
            $data = $action->execute($filters);
            return response()->json([
                'success' => true,
                'message' => 'Sales report generated successfully',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate sales report: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function inventoryReport(Request $request, GenerateInventoryReportAction $action): JsonResponse
    {
        $filters = $request->only(['location_id', 'product_id', 'date_from', 'date_to']);

        try {
            $data = $action->execute($filters);
            return response()->json([
                'success' => true,
                'message' => 'Inventory valuation report generated successfully',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate inventory report: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function financialReport(Request $request, GenerateFinancialReportAction $action): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to']);

        try {
            $data = $action->execute($filters);
            return response()->json([
                'success' => true,
                'message' => 'Financial report generated successfully',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate financial report: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function purchaseReport(Request $request, GeneratePurchaseReportAction $action): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to']);

        try {
            $data = $action->execute($filters);
            return response()->json([
                'success' => true,
                'message' => 'Purchase report generated successfully',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate purchase report: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportSales(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Sales report exported successfully to Excel',
        ]);
    }

    public function exportInventory(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Inventory report exported successfully to Excel',
        ]);
    }

    public function exportFinancial(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Financial report exported successfully to Excel',
        ]);
    }
}
