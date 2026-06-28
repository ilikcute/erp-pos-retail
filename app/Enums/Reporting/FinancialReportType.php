<?php

namespace App\Enums\Reporting;

enum FinancialReportType: string
{
    case PROFIT_LOSS = 'PROFIT_LOSS';
    case BALANCE_SHEET = 'BALANCE_SHEET';
    case CASH_FLOW = 'CASH_FLOW';
    case CHANGES_IN_EQUITY = 'CHANGES_IN_EQUITY';
    case TRIAL_BALANCE = 'TRIAL_BALANCE';
    case GENERAL_LEDGER = 'GENERAL_LEDGER';

    public function label(): string
    {
        return match ($this) {
            self::PROFIT_LOSS => 'Laba Rugi',
            self::BALANCE_SHEET => 'Neraca',
            self::CASH_FLOW => 'Arus Kas',
            self::CHANGES_IN_EQUITY => 'Perubahan Ekuitas',
            self::TRIAL_BALANCE => 'Neraca Saldo',
            self::GENERAL_LEDGER => 'Buku Besar',
        };
    }
}
