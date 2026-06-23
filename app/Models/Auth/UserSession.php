<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\System\User;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'ip_address',
        'user_agent',
        'device_name',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return ['last_activity_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
