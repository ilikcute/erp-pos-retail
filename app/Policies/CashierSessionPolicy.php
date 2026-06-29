<?php

namespace App\Policies;

use App\Models\System\User;
use App\Models\POS\CashierSession;

class CashierSessionPolicy
{
    /**
     * Tentukan apakah user bisa menutup sesi kasir.
     */
    public function close(User $user, CashierSession $session): bool
    {
        // Pemilik sesi bisa menutup sesinya sendiri
        if ($session->user_id === $user->id) {
            return true;
        }

        // Admin, supervisor, atau yang memiliki permission pos.sessions.close_any bisa menutup sesi siapa saja
        return $user->hasRole('admin') 
            || $user->hasRole('supervisor') 
            || $user->hasPermission('pos.sessions.close_any');
    }
}
