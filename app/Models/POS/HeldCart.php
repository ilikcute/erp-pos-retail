<?php

namespace App\Models\POS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HeldCart extends Model
{
    protected $fillable = ['user_id', 'label'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(HeldCartItem::class);
    }
}
