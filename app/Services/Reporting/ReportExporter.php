<?php

namespace App\Services\Reporting;

use App\Enums\Reporting\ReportFormat;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExporter
{
    /**
     * Export report to specified format
     */
    public function export(array $reportData, string $reportName, ReportFormat $format)
    {
        return match ($format) {
            ReportFormat::CSV => $this->exportToCsv($reportData, $reportName),
            ReportFormat::PDF => $this->exportToPdf($reportData, $reportName),
            ReportFormat::EXCEL => $this->exportToExcel($reportData, $reportName),
            default => $reportData,
        };
    }

    private function exportToCsv(array $data, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Header row
            if (! empty($data['data'])) {
                fputcsv($handle, array_keys($data['data'][0]));

                // Data rows
                foreach ($data['data'] as $row) {
                    fputcsv($handle, $row);
                }
            }

            fclose($handle);
        }, "{$filename}.csv", $headers);
    }

    private function exportToPdf(array $data, string $filename)
    {
        // Requires barryvdh/laravel-dompdf or similar package
        // Implementation depends on PDF library used
        throw new \RuntimeException('PDF export not yet implemented. Install barryvdh/laravel-dompdf first.');
    }

    private function exportToExcel(array $data, string $filename)
    {
        // Requires phpoffice/phpspreadsheet or maatwebsite/excel
        throw new \RuntimeException('Excel export not yet implemented. Install maatwebsite/excel first.');
    }
}
