<?php

namespace App\Policies;

use App\Models\POS\SalesTransaction;
use App\Models\System\User;

class SalesTransactionPolicy
{
    /**
     * Tentukan apakah user bisa melihat transaksi penjualan.
     */
    public function view(User $user, SalesTransaction $transaction): bool
    {
        // Admin atau supervisor bisa melihat semua transaksi
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return true;
        }

        // Kasir biasa hanya boleh melihat transaksi dari lokasinya sendiri
        $activeLocationId = session('pos_location_id');

        return $transaction->session && $transaction->session->location_id === $activeLocationId;
    }
}
