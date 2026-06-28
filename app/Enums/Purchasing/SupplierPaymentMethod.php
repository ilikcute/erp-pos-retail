<?php

namespace App\Enums\Purchasing;

enum SupplierPaymentMethod: string
{
    case CASH = 'CASH';
    case TRANSFER = 'TRANSFER';
    case GIRO = 'GIRO';
    case CHEQUE = 'CHEQUE';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tunai',
            self::TRANSFER => 'Transfer Bank',
            self::GIRO => 'Giro',
            self::CHEQUE => 'Cek',
        };
    }
}
