<?php

namespace App\Actions\Security;

use App\Models\Auth\UserSession;
use App\Models\System\User;

class ManageDevicesAction
{
    public function registerDevice(User $user, array $deviceInfo): UserSession
    {
        return UserSession::create([
            'user_id' => $user->id,
            'device_name' => $deviceInfo['device_name'] ?? 'Unknown Device',
            'device_type' => $deviceInfo['device_type'] ?? 'WEB',
            'user_agent' => $deviceInfo['user_agent'],
            'ip_address' => $deviceInfo['ip_address'],
            'trusted_until' => now()->addDays(30),
            'last_activity_at' => now(),
            'is_trusted' => false,
        ]);
    }

    public function trustDevice(UserSession $session): void
    {
        $session->update([
            'is_trusted' => true,
            'trusted_until' => now()->addDays(90),
        ]);
    }

    public function revokeDevice(UserSession $session): void
    {
        $session->delete();
    }

    public function getUserDevices(User $user)
    {
        return $user->sessions()
            ->where('last_activity_at', '>=', now()->subDays(90))
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    public function revokeAllDevices(User $user): void
    {
        $user->sessions()->delete();
    }
}
