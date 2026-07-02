<?php

namespace App\Actions\Security;

use App\Models\System\User;
use Illuminate\Support\Facades\DB;

class ManageIPWhitelistAction
{
    public function addIPAddress(User $user, string $ipAddress, ?string $description = null): void
    {
        DB::table('user_ip_whitelist')->insert([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'description' => $description,
            'created_at' => now(),
        ]);
    }

    public function removeIPAddress(User $user, string $ipAddress): void
    {
        DB::table('user_ip_whitelist')
            ->where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->delete();
    }

    public function isIPWhitelisted(User $user, string $ipAddress): bool
    {
        if (! $user->ip_whitelist_enabled) {
            return true;
        }

        return DB::table('user_ip_whitelist')
            ->where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->exists();
    }

    public function getWhitelistedIPs(User $user)
    {
        return DB::table('user_ip_whitelist')
            ->where('user_id', $user->id)
            ->get();
    }

    public function toggleIPWhitelist(User $user, bool $enabled): void
    {
        $user->update(['ip_whitelist_enabled' => $enabled]);
    }
}
