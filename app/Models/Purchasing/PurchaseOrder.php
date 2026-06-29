<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MasterData\Supplier;
use App\Models\Auth\User; // or App\Models\System\User depending on workspace

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number',
        'purchase_request_id',
        'supplier_id',
        'order_date',
        'expected_date',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'remarks',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\System\User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\System\User::class, 'approved_by');
    }
}
