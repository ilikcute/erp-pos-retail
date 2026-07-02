<?php

namespace App\Repositories\Contracts\System;

use App\Models\System\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): void;

    public function assignRoles(User $user, array $roleIds): void;

    public function syncRoles(User $user, array $roleIds): void;

    public function listAll(): Collection;
}
