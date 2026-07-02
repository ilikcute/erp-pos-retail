<?php

namespace App\Repositories\Eloquent\System;

use App\Models\System\Role;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Role::query()
            ->with('permissions')
            ->when(
                $filters['search'] ?? null,
                fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                    ->orWhere('display_name', 'like', "%{$s}%")
            )
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role->fresh();
    }

    public function delete(Role $role): void
    {
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    public function listAll(): Collection
    {
        return Role::active()->with('permissions')->orderBy('display_name')->get();
    }
}
