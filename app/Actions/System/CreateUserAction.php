<?php

namespace App\Actions\System;

use App\Models\System\User;
use App\Repositories\Contracts\System\UserRepositoryInterface;

class CreateUserAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(array $data): User
    {
        // Pisahkan roles dari data utama
        $roleIds = $data['roles'] ?? [];
        unset($data['roles']);

        // Create user melalui repository
        $user = $this->userRepository->create($data);

        // Sync roles jika ada
        if (!empty($roleIds)) {
            $this->userRepository->syncRoles($user, $roleIds);
        }

        return $user->load('roles.permissions');
    }
}
