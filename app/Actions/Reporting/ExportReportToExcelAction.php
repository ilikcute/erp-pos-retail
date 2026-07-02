<?php

namespace App\Actions\Reporting;

use App\Exports\FinancialReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\MarginReportExport;
use App\Exports\SalesReportExport;

class ExportReportToExcelAction
{
    public function execute(string $reportType, array $data, string $filename): string
    {
        $storageDir = storage_path('app/public/exports');

        if (! is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $fileName = "{$filename}_".now()->timestamp.'.xlsx';
        $filePath = "{$storageDir}/{$fileName}";

        $exporter = $this->getExporter($reportType, $data);
        $exporter->export($filePath);

        return "exports/{$fileName}";
    }

    private function getExporter(string $reportType, array $data): object
    {
        return match ($reportType) {
            'sales' => new SalesReportExport($data),
            'inventory' => new InventoryReportExport($data),
            'financial' => new FinancialReportExport($data),
            'margin' => new MarginReportExport($data),
            default => throw new \InvalidArgumentException("Unknown report type: {$reportType}"),
        };
    }
}
