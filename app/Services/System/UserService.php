<?php

namespace App\Services\System;

use App\Enums\UserStatus;
use App\Models\System\User;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? UserStatus::ACTIVE->value;
        $data['force_password_change'] = $data['force_password_change'] ?? true;

        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        $user = $this->userRepository->create($data);

        if ($roleIds) {
            $this->userRepository->syncRoles($user, $roleIds);
        }

        $this->auditService->log(
            module: 'System',
            action: 'CREATE_USER',
            tableName: 'users',
            recordId: $user->id,
            newValues: ['name' => $user->name, 'email' => $user->email],
        );

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $original = $user->only(['name', 'email', 'status']);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $roleIds = $data['role_ids'] ?? null;
        unset($data['role_ids']);

        $user = $this->userRepository->update($user, $data);

        if ($roleIds !== null) {
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

        return $user;
    }

    public function delete(User $user): void
    {
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
