<?php

namespace App\Enums\Accounting;

enum AccountType: string
{
    case ASSET = 'ASSET';
    case LIABILITY = 'LIABILITY';
    case EQUITY = 'EQUITY';
    case REVENUE = 'REVENUE';
    case EXPENSE = 'EXPENSE';

    public function label(): string
    {
        return match ($this) {
            self::ASSET => 'Aset',
            self::LIABILITY => 'Kewajiban',
            self::EQUITY => 'Ekuitas',
            self::REVENUE => 'Pendapatan',
            self::EXPENSE => 'Beban',
        };
    }

    public function defaultNormalBalance(): NormalBalance
    {
        return match ($this) {
            self::ASSET, self::EXPENSE => NormalBalance::DEBIT,
            self::LIABILITY, self::EQUITY, self::REVENUE => NormalBalance::CREDIT,
        };
    }
}
