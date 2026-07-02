<?php

namespace App\Http\Controllers\Api\Reporting;

use App\Enums\Reporting\ReportFormat;
use App\Enums\Reporting\ReportGroupBy;
use App\Http\Controllers\Controller;
use App\Services\Reporting\ReportExporter;
use App\Services\Reporting\SalesReportService;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function __construct(
        private readonly SalesReportService $salesService,
        private readonly ReportExporter $exporter
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'group_by' => 'required|in:day,week,month,year,product,category,cashier,customer,payment_method,location',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'cashier_id' => 'nullable|integer|exists:users,id',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
            'payment_method' => 'nullable|string',
            'format' => 'nullable|in:json,csv,pdf,excel',
        ]);

        $report = $this->salesService->generate(
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
            groupBy: ReportGroupBy::from($validated['group_by']),
            customerId: $validated['customer_id'] ?? null,
            cashierId: $validated['cashier_id'] ?? null,
            locationId: $validated['location_id'] ?? null,
            paymentMethod: $validated['payment_method'] ?? null,
        );

        // Export if format specified
        if (isset($validated['format']) && $validated['format'] !== 'json') {
            $format = ReportFormat::from($validated['format']);

            return $this->exporter->export($report, 'sales_report', $format);
        }

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function topProducts(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'limit' => 'nullable|integer|min:1|max:100',
            'category_id' => 'nullable|integer|exists:product_categories,id',
        ]);

        $report = $this->salesService->topProducts(
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
            limit: $validated['limit'] ?? 10,
            categoryId: $validated['category_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function hourlyPattern(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $report = $this->salesService->hourlyPattern(
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
