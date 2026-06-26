<?php

namespace App\Actions\System;

use App\Models\System\User;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Support\AuditService;

class CreateUserAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuditService $auditService
    ) {}

    public function execute(array $data): User
    {
        // Pisahkan roles dari data utama
        $roleIds = $data['roles'] ?? [];
        unset($data['roles']);

        // Create user melalui repository
        $user = $this->userRepository->create($data);

        // Sync roles jika ada
        if (! empty($roleIds)) {
            $this->userRepository->syncRoles($user, $roleIds);
        }

        $this->auditService->log(
            module: 'System',
            action: 'CREATE_USER',
            tableName: 'users',
            recordId: $user->id,
            newValues: ['name' => $user->name, 'email' => $user->email],
        );

        return $user->load('roles.permissions');
    }
}
