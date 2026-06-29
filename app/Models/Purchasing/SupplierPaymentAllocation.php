<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;

class SupplierPaymentAllocation extends Model
{
    protected $fillable = [
        'supplier_payment_id',
        'account_payable_id',
        'allocated_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(SupplierPayment::class, 'supplier_payment_id');
    }

    public function accountPayable()
    {
        return $this->belongsTo(AccountsPayable::class, 'account_payable_id');
    }
}
