<?php

namespace App\Models\Accounting;

use App\Enums\Accounting\PaymentMethodType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'method_code',
        'method_name',
        'method_type',
        'account_id',
        'gateway_code',
        'logo_url',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'method_type' => PaymentMethodType::class,
        'is_active' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function isCash(): bool
    {
        return $this->method_type->isCash();
    }
}
