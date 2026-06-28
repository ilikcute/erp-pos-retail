<?php

namespace App\Enums\Reporting;

enum ReportFormat: string
{
    case JSON = 'json';
    case CSV = 'csv';
    case PDF = 'pdf';
    case EXCEL = 'excel';

    public function label(): string
    {
        return match ($this) {
            self::JSON => 'JSON',
            self::CSV => 'CSV',
            self::PDF => 'PDF',
            self::EXCEL => 'Excel',
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::JSON => 'application/json',
            self::CSV => 'text/csv',
            self::PDF => 'application/pdf',
            self::EXCEL => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }
}
