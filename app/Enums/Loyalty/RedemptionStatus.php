<?php

namespace App\Enums\Loyalty;

enum RedemptionStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case REDEEMED = 'REDEEMED';
    case CANCELLED = 'CANCELLED';
}
