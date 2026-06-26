<?php

namespace App\Actions\System;

use App\Exceptions\BusinessException;
use App\Models\System\User;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Support\AuditService;

class DeleteUserAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuditService $auditService
    ) {}

    public function execute(User $user): void
    {
        // Validasi: User tidak bisa menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            throw new BusinessException(
                message: 'Cannot delete your own account',
                errors: ['user' => 'You cannot delete the account you are currently logged in with']
            );
        }

        $this->auditService->log(
            module: 'System',
            action: 'DELETE_USER',
            tableName: 'users',
            recordId: $user->id,
            oldValues: ['name' => $user->name, 'email' => $user->email],
        );

        $this->userRepository->delete($user);
    }
}
