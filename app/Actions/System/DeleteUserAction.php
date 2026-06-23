<?php

namespace App\Actions\System;

use App\Models\System\User;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Exceptions\BusinessException;

class DeleteUserAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository
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

        $this->userRepository->delete($user);
    }
}
