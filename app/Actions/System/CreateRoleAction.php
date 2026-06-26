<?php

namespace App\Actions\System;

use App\Models\System\Role;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Support\AuditService;

class CreateRoleAction
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuditService $auditService
    ) {}

    public function execute(array $data): Role
    {
        // Pisahkan permissions dari data utama
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        // Create role melalui repository
        $role = $this->roleRepository->create($data);

        // Sync permissions jika ada
        if (! empty($permissionIds)) {
            $this->roleRepository->syncPermissions($role, $permissionIds);
        }

        $this->auditService->log(
            module: 'System',
            action: 'CREATE_ROLE',
            tableName: 'roles',
            recordId: $role->id,
            newValues: ['name' => $role->name, 'display_name' => $role->display_name],
        );

        return $role->load('permissions');
    }
}
