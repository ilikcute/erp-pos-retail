<?php

namespace App\Repositories\Eloquent\Auth;

use App\Models\System\User;
use App\Repositories\Contracts\Auth\UserAuthRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentUserAuthRepository implements UserAuthRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function updateLastLogin(int $userId): void
    {
        User::where('id', $userId)->update(['last_login_at' => now()]);
    }

    public function updatePassword(int $userId, string $hashedPassword): void
    {
        User::where('id', $userId)->update([
            'password' => $hashedPassword,
            'force_password_change' => false,
        ]);
    }

    public function storePasswordResetToken(string $email, string $token): void
    {
        DB::table('password_reset_tokens')->upsert(
            ['email' => $email, 'token' => $token, 'created_at' => now()],
            ['email'],
        );
    }

    public function findPasswordResetToken(string $token): ?object
    {
        return DB::table('password_reset_tokens')->where('token', $token)->first();
    }

    public function deletePasswordResetToken(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}
