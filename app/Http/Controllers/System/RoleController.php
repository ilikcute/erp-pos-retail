<?php

namespace App\Http\Controllers\System;

use App\Actions\System\CreateRoleAction;
use App\Actions\System\DeleteRoleAction;
use App\Actions\System\UpdateRoleAction;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreRoleRequest;
use App\Http\Requests\System\UpdateRoleRequest;
use App\Repositories\Contracts\System\PermissionRepositoryInterface;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private PermissionRepositoryInterface $permissionRepository
    ) {}

    public function index(): Response
    {
        $roles = $this->roleRepository->paginate(request()->only('search'), 15);
        $permissions = $this->permissionRepository->getAll();

        return Inertia::render('System/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request, CreateRoleAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('system.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function update(int $id, UpdateRoleRequest $request, UpdateRoleAction $action): RedirectResponse
    {
        $role = $this->roleRepository->findById($id);
        if (! $role) {
            abort(404);
        }

        $action->execute($role, $request->validated());

        return redirect()->route('system.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(int $id, DeleteRoleAction $action): RedirectResponse
    {
        $role = $this->roleRepository->findById($id);
        if (! $role) {
            abort(404);
        }

        try {
            $action->execute($role);

            return redirect()->route('system.roles.index')
                ->with('success', 'Role deleted successfully.');
        } catch (BusinessException $e) {
            // Tangkap error business rule (misal: hapus system role) agar tidak jadi halaman error 500
            return redirect()->route('system.roles.index')
                ->withErrors(['delete' => $e->getMessage()]);
        }
    }
}
