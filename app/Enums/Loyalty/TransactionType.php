<?php

namespace App\Enums\Loyalty;

enum TransactionType: string
{
    case EARN = 'EARN';              // Dapat poin dari transaksi
    case REDEEM = 'REDEEM';          // Tukar poin ke reward
    case ADJUSTMENT = 'ADJUSTMENT';  // Adjustment manual
    case EXPIRE = 'EXPIRE';          // Poin kadaluarsa
    case REFUND = 'REFUND';          // Refund dari redeem

    public function isIncrease(): bool
    {
        return in_array($this, [self::EARN, self::REFUND]);
    }

    public function label(): string
    {
        return match ($this) {
            self::EARN => 'Perolehan',
            self::REDEEM => 'Penukaran',
            self::ADJUSTMENT => 'Penyesuaian',
            self::EXPIRE => 'Kadaluarsa',
            self::REFUND => 'Pengembalian',
        };
    }
}
