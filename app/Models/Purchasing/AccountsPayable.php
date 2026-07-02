<?php

namespace App\Models\Purchasing;

use App\Models\MasterData\Supplier;
use Illuminate\Database\Eloquent\Model;

class AccountsPayable extends Model
{
    protected $table = 'accounts_payables';

    protected $fillable = [
        'payable_number',
        'supplier_id',
        'invoice_id',
        'source_type',
        'source_id',
        'transaction_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'currency',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice()
    {
        return $this->belongsTo(SupplierInvoice::class, 'invoice_id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
