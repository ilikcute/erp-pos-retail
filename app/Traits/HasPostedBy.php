<?php

namespace App\Traits;

use App\Models\System\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPostedBy
{
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function markAsPosted(int $userId): void
    {
        $this->forceFill([
            'status' => 'POSTED',
            'posted_by' => $userId,
            'posted_at' => now(),
        ])->save();
    }

    public function isPosted(): bool
    {
        return $this->status === 'POSTED';
    }
}
