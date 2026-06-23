<?php

namespace App\Services\Auth;

use App\Models\System\User;
use App\Repositories\Contracts\Auth\UserAuthRepositoryInterface;
use App\Support\AuditService;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserAuthRepositoryInterface $userRepository,
        private readonly AuditService $auditService,
    ) {}

    /**
     * Attempt login, kembalikan token Sanctum.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, string $deviceName = 'web'): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak valid.'],
            ]);
        }

        if ($user->status !== UserStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'email' => ['Akun tidak aktif. Hubungi administrator.'],
            ]);
        }

        // Revoke token lama dari device yang sama jika ada
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        $this->userRepository->updateLastLogin($user->id);

        $this->auditService->activity('LOGIN', 'Auth', "Login dari device: {$deviceName}");

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout — revoke current token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();

        $this->auditService->activity('LOGOUT', 'Auth');
    }

    /**
     * Logout semua device.
     */
    public function logoutAllDevices(User $user): void
    {
        $user->tokens()->delete();

        $this->auditService->activity('LOGOUT_ALL', 'Auth', 'Logout dari semua perangkat');
    }

    /**
     * Ganti password.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak benar.'],
            ]);
        }

        $this->userRepository->updatePassword($user->id, Hash::make($newPassword));

        $this->auditService->activity('CHANGE_PASSWORD', 'Auth');
    }
}
