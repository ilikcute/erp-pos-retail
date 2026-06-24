<?php

namespace App\Actions\Reporting;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;

class ExportReportToExcelAction
{
    public function execute(string $reportType, array $data, string $filename): string
    {
        $exportClass = $this->getExportClass($reportType);
        
        $filePath = "exports/{$filename}_" . now()->timestamp . ".xlsx";
        Excel::store(new $exportClass($data), $filePath, 'local');

        return $filePath;
    }

    private function getExportClass(string $reportType): string
    {
        return match($reportType) {
            'sales' => \App\Exports\SalesReportExport::class,
            'inventory' => \App\Exports\InventoryReportExport::class,
            'financial' => \App\Exports\FinancialReportExport::class,
            'dashboard' => \App\Exports\DashboardExport::class,
            default => throw new \InvalidArgumentException("Unknown report type: {$reportType}"),
        };
    }
}
