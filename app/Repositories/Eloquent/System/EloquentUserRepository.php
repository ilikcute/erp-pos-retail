<?php

namespace App\Repositories\Eloquent\System;

use App\Models\System\User;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when(
                $filters['search'] ?? null,
                fn($q, $s) =>
                $q->where(
                    fn($q) => $q
                        ->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                )
            )
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when(
                $filters['role_id'] ?? null,
                fn($q, $roleId) =>
                $q->whereHas('roles', fn($q) => $q->where('roles.id', $roleId))
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }

    public function listAll(): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('roles')->orderBy('name')->get();
    }

    public function assignRoles(User $user, array $roleIds): void
    {
        $user->roles()->attach($roleIds);
    }

    public function syncRoles(User $user, array $roleIds): void
    {
        $user->roles()->sync($roleIds);
    }
}
