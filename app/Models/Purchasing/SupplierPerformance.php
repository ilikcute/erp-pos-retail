<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Supplier;
use App\Models\System\User;

class SupplierPerformance extends Model
{
    protected $table = 'supplier_performances';

    protected $fillable = [
        'supplier_id',
        'evaluation_period',
        'on_time_delivery_score',
        'price_score',
        'quality_score',
        'service_score',
        'overall_score',
        'notes',
        'evaluated_by',
    ];

    protected $casts = [
        'evaluation_period' => 'date',
        'on_time_delivery_score' => 'decimal:2',
        'price_score' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'service_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
