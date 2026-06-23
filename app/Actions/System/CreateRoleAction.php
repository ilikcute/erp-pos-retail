<?php

namespace App\Actions\System;

use App\Models\System\Role;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Services\System\CodeGeneratorService;

class CreateRoleAction
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {}

    public function execute(array $data): Role
    {
        // Pisahkan permissions dari data utama
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        // Create role melalui repository
        $role = $this->roleRepository->create($data);

        // Sync permissions jika ada
        if (!empty($permissionIds)) {
            $this->roleRepository->syncPermissions($role, $permissionIds);
        }

        return $role->load('permissions');
    }
}
