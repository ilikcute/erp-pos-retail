<?php

namespace App\Enums\Loyalty;

enum AdjustmentType: string
{
    case ADD = 'ADD';
    case DEDUCT = 'DEDUCT';

    public function isIncrease(): bool
    {
        return $this === self::ADD;
    }

    public function label(): string
    {
        return match ($this) {
            self::ADD => 'Tambah Poin',
            self::DEDUCT => 'Kurangi Poin',
        };
    }
}
