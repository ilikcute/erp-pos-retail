<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\System\User;

class PurchaseRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pr_number',
        'request_date',
        'requested_by',
        'status',
        'remarks',
        'approved_by',
        'approved_at',
        'rejection_notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
