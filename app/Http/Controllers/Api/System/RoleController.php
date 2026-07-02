<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\RoleResource;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('system.role.view');

        $roles = $this->roleRepository->paginate(
            filters: $request->only(['search', 'is_active']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => RoleResource::collection($roles->items()),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('system.role.view');

        $role = $this->roleRepository->findById($id);
        abort_if(! $role, 404, 'Role tidak ditemukan.');

        return response()->json(['data' => new RoleResource($role)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('system.role.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name', 'regex:/^[a-z0-9_-]+$/'],
            'display_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $permissionIds = $validated['permission_ids'] ?? [];
        unset($validated['permission_ids']);

        $role = $this->roleRepository->create($validated);

        if ($permissionIds) {
            $this->roleRepository->syncPermissions($role, $permissionIds);
        }

        $this->auditService->log('System', 'CREATE_ROLE', 'roles', $role->id, [], ['name' => $role->name]);

        return response()->json([
            'data' => new RoleResource($role->load('permissions')),
            'message' => 'Role berhasil dibuat.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('system.role.manage');

        $role = $this->roleRepository->findById($id);
        abort_if(! $role, 404, 'Role tidak ditemukan.');
        abort_if($role->name === 'superadmin', 422, 'Role superadmin tidak dapat diubah.');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($id), 'regex:/^[a-z0-9_-]+$/'],
            'display_name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $permissionIds = $validated['permission_ids'] ?? null;
        unset($validated['permission_ids']);

        $original = $role->only(['name', 'display_name', 'is_active']);
        $role = $this->roleRepository->update($role, $validated);

        if ($permissionIds !== null) {
            $this->roleRepository->syncPermissions($role, $permissionIds);
        }

        $this->auditService->log('System', 'UPDATE_ROLE', 'roles', $role->id, $original, $role->only(['name', 'display_name', 'is_active']));

        return response()->json([
            'data' => new RoleResource($role->load('permissions')),
            'message' => 'Role berhasil diperbarui.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('system.role.manage');

        $role = $this->roleRepository->findById($id);
        abort_if(! $role, 404, 'Role tidak ditemukan.');
        abort_if(in_array($role->name, ['superadmin', 'admin']), 422, 'Role ini tidak dapat dihapus.');
        abort_if($role->users()->exists(), 422, 'Role masih digunakan oleh user aktif.');

        $this->auditService->log('System', 'DELETE_ROLE', 'roles', $role->id, ['name' => $role->name], []);
        $this->roleRepository->delete($role);

        return response()->json(['message' => 'Role berhasil dihapus.']);
    }

    public function syncPermissions(Request $request, int $id): JsonResponse
    {
        $this->authorize('system.role.manage');

        $role = $this->roleRepository->findById($id);
        abort_if(! $role, 404, 'Role tidak ditemukan.');

        $validated = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $this->roleRepository->syncPermissions($role, $validated['permission_ids']);
        $this->auditService->log('System', 'SYNC_ROLE_PERMISSIONS', 'role_permissions', $role->id);

        return response()->json(['message' => 'Permissions role berhasil disinkronkan.']);
    }
}
