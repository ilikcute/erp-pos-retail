<?php

namespace App\Actions\System;

use App\Models\System\User;
use App\Repositories\Contracts\System\UserRepositoryInterface;

class UpdateUserAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(User $user, array $data): User
    {
        // Jangan update password jika kosong
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Pisahkan roles dari data utama
        $roleIds = $data['roles'] ?? [];
        unset($data['roles']);

        // Update user melalui repository
        $this->userRepository->update($user, $data);

        // Sync roles jika ada
        if (isset($data['roles']) || !empty($roleIds)) {
            $this->userRepository->syncRoles($user, $roleIds);
        }

        return $user->fresh()->load('roles.permissions');
    }
}
