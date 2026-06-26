<?php

namespace App\Actions\System;

use App\Models\System\Role;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Support\AuditService;

class UpdateRoleAction
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuditService $auditService
    ) {}

    public function execute(Role $role, array $data): Role
    {
        $original = $role->only(['name', 'display_name', 'is_active']);

        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $this->roleRepository->update($role, $data);

        if (isset($data['permissions']) || ! empty($permissionIds)) {
            $this->roleRepository->syncPermissions($role, $permissionIds);
        }

        $this->auditService->log(
            module: 'System',
            action: 'UPDATE_ROLE',
            tableName: 'roles',
            recordId: $role->id,
            oldValues: $original,
            newValues: $role->only(['name', 'display_name', 'is_active']),
        );

        return $role->fresh()->load('permissions');
    }
}
