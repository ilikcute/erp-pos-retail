<?php

namespace App\Http\Controllers\Api\Reporting;

use App\Actions\Reporting\ExportReportToExcelAction;
use App\Actions\Reporting\GenerateFinancialReportAction;
use App\Actions\Reporting\GenerateInventoryReportAction;
use App\Actions\Reporting\GenerateMarginReportAction;
use App\Actions\Reporting\GeneratePurchaseReportAction;
use App\Actions\Reporting\GenerateSalesReportAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate sales report: '.$e->getMessage(),
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
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate inventory report: '.$e->getMessage(),
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
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate financial report: '.$e->getMessage(),
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
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate purchase report: '.$e->getMessage(),
            ], 500);
        }
    }

    public function marginReport(Request $request, GenerateMarginReportAction $action): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'location_id', 'product_id']);

        try {
            $data = $action->execute($filters);

            return response()->json([
                'success' => true,
                'message' => 'Sales margin report generated successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate margin report: '.$e->getMessage(),
            ], 500);
        }
    }

    public function exportSales(Request $request, GenerateSalesReportAction $salesAction, ExportReportToExcelAction $exportAction): BinaryFileResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'cashier_id', 'customer_id']);
        $data = $salesAction->execute($filters);

        // Convert Eloquent collection data to array-friendly format
        $exportData = [
            'transactions' => $data['transactions'],
        ];

        $relativePath = $exportAction->execute('sales', $exportData, 'laporan_penjualan');
        $fullPath = storage_path('app/public/'.$relativePath);

        return response()->download($fullPath, 'Laporan_Penjualan_'.now()->format('Y-m-d').'.xlsx')->deleteFileAfterSend(true);
    }

    public function exportInventory(Request $request, GenerateInventoryReportAction $inventoryAction, ExportReportToExcelAction $exportAction): BinaryFileResponse
    {
        $filters = $request->only(['location_id', 'product_id', 'date_from', 'date_to']);
        $data = $inventoryAction->execute($filters);

        $relativePath = $exportAction->execute('inventory', $data, 'laporan_inventori');
        $fullPath = storage_path('app/public/'.$relativePath);

        return response()->download($fullPath, 'Laporan_Inventori_'.now()->format('Y-m-d').'.xlsx')->deleteFileAfterSend(true);
    }

    public function exportFinancial(Request $request, GenerateFinancialReportAction $financialAction, ExportReportToExcelAction $exportAction): BinaryFileResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $data = $financialAction->execute($filters);

        $relativePath = $exportAction->execute('financial', $data, 'laporan_keuangan');
        $fullPath = storage_path('app/public/'.$relativePath);

        return response()->download($fullPath, 'Laporan_Keuangan_'.now()->format('Y-m-d').'.xlsx')->deleteFileAfterSend(true);
    }

    public function exportMargin(Request $request, GenerateMarginReportAction $marginAction, ExportReportToExcelAction $exportAction): BinaryFileResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'location_id', 'product_id']);
        $data = $marginAction->execute($filters);

        $relativePath = $exportAction->execute('margin', $data, 'laporan_margin');
        $fullPath = storage_path('app/public/'.$relativePath);

        return response()->download($fullPath, 'Laporan_Margin_'.now()->format('Y-m-d').'.xlsx')->deleteFileAfterSend(true);
    }
}
