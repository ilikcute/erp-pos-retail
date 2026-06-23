<?php

namespace App\Actions\System;

use App\Models\System\Role;
use App\Repositories\Contracts\System\RoleRepositoryInterface;

class UpdateRoleAction
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {}

    public function execute(Role $role, array $data): Role
    {
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $this->roleRepository->update($role, $data);

        if (isset($data['permissions']) || !empty($permissionIds)) {
            $this->roleRepository->syncPermissions($role, $permissionIds);
        }

        return $role->fresh()->load('permissions');
    }
}
