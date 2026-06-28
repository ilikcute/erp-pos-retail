<?php

namespace App\Http\Controllers\Api\Reporting;

use App\Enums\Reporting\FinancialReportType;
use App\Http\Controllers\Controller;
use App\Services\Reporting\FinancialReportService;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function __construct(
        private readonly FinancialReportService $financialService
    ) {}

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:PROFIT_LOSS,BALANCE_SHEET,CASH_FLOW,TRIAL_BALANCE,GENERAL_LEDGER',
            'date_from' => 'required_if:report_type,PROFIT_LOSS,CASH_FLOW,GENERAL_LEDGER|date',
            'date_to' => 'required_if:report_type,PROFIT_LOSS,CASH_FLOW,GENERAL_LEDGER|date|after_or_equal:date_from',
            'as_of_date' => 'required_if:report_type,BALANCE_SHEET,TRIAL_BALANCE|date',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
        ]);

        $reportType = FinancialReportType::from($validated['report_type']);

        // Determine dates based on report type
        if (in_array($reportType, [FinancialReportType::BALANCE_SHEET, FinancialReportType::TRIAL_BALANCE])) {
            $dateFrom = $validated['as_of_date'];
            $dateTo = $validated['as_of_date'];
        } else {
            $dateFrom = $validated['date_from'];
            $dateTo = $validated['date_to'];
        }

        $report = $this->financialService->generate(
            reportType: $reportType,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            locationId: $validated['location_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
