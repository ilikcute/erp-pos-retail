<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Supplier;
use App\Models\Accounting\PaymentMethod;
use App\Models\System\User;

class SupplierPayment extends Model
{
    protected $fillable = [
        'payment_number',
        'supplier_id',
        'payment_date',
        'payment_method',
        'reference_no',
        'payment_method_account_id',
        'total_amount',
        'status',
        'remarks',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paymentMethodAccount()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_account_id');
    }

    public function allocations()
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
