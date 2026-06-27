<?php

namespace App\Enums\Inventory;

enum TransferStatus: string
{
    case DRAFT = 'DRAFT';
    case POSTED = 'POSTED';
    case CANCELLED = 'CANCELLED';
}

enum AdjustmentStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case POSTED = 'POSTED';
}

enum OpnameStatus: string
{
    case DRAFT = 'DRAFT';
    case COUNTED = 'COUNTED';
    case APPROVED = 'APPROVED';
    case POSTED = 'POSTED';
}
