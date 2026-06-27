<?php

namespace App\Enums\Accounting;

enum PaymentMethodType: string
{
    case CASH = 'CASH';
    case QRIS = 'QRIS';
    case DEBIT = 'DEBIT';
    case CREDIT_CARD = 'CREDIT_CARD';
    case TRANSFER = 'TRANSFER';
    case LOYALTY_POINT = 'LOYALTY_POINT';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tunai',
            self::QRIS => 'QRIS',
            self::DEBIT => 'Kartu Debit',
            self::CREDIT_CARD => 'Kartu Kredit',
            self::TRANSFER => 'Transfer Bank',
            self::LOYALTY_POINT => 'Poin Loyalty',
            self::OTHER => 'Lainnya',
        };
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }
}
