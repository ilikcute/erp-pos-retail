<?php

namespace App\Actions\System;

use App\Models\System\User;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Support\AuditService;

class UpdateUserAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuditService $auditService
    ) {}

    public function execute(User $user, array $data): User
    {
        $original = $user->only(['name', 'email', 'status']);

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
        if (isset($data['roles']) || ! empty($roleIds)) {
            $this->userRepository->syncRoles($user, $roleIds);
        }

        $this->auditService->log(
            module: 'System',
            action: 'UPDATE_USER',
            tableName: 'users',
            recordId: $user->id,
            oldValues: $original,
            newValues: $user->only(['name', 'email', 'status']),
        );

        return $user->fresh()->load('roles.permissions');
    }
}
