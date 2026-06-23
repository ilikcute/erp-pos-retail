<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'decimal_places', 'exchange_rate', 'is_base', 'is_active'];

    protected function casts(): array
    {
        return ['exchange_rate' => 'decimal:6', 'is_base' => 'boolean', 'is_active' => 'boolean'];
    }
}
