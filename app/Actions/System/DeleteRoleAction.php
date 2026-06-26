<?php

namespace App\Actions\System;

use App\Exceptions\BusinessException;
use App\Models\System\Role;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Support\AuditService;

class DeleteRoleAction
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuditService $auditService
    ) {}

    public function execute(Role $role): void
    {
        // Validasi: System role tidak boleh dihapus
        if ($role->is_system) {
            throw new BusinessException(
                message: 'System roles cannot be deleted',
                errors: ['role' => 'This is a system role and cannot be deleted']
            );
        }

        // Validasi: Role yang sedang digunakan user tidak boleh dihapus
        if ($role->users()->count() > 0) {
            throw new BusinessException(
                message: 'Cannot delete role that is assigned to users',
                errors: ['role' => 'This role is currently assigned to '.$role->users()->count().' user(s)']
            );
        }

        $this->auditService->log(
            module: 'System',
            action: 'DELETE_ROLE',
            tableName: 'roles',
            recordId: $role->id,
            oldValues: ['name' => $role->name, 'display_name' => $role->display_name],
        );

        $this->roleRepository->delete($role);
    }
}
