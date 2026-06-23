<?php

namespace App\Repositories\Contracts\Auth;

use App\Models\System\User;

interface UserAuthRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;

    public function updateLastLogin(int $userId): void;

    public function updatePassword(int $userId, string $hashedPassword): void;

    public function storePasswordResetToken(string $email, string $token): void;

    public function findPasswordResetToken(string $token): ?object;

    public function deletePasswordResetToken(string $email): void;
}
