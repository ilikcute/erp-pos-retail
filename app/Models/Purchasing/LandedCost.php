<?php

namespace App\Models\Purchasing;

use App\Models\System\User;
use Illuminate\Database\Eloquent\Model;

class LandedCost extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'cost_type',
        'amount',
        'allocation_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
