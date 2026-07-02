<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                // Group by module prefix (e.g., "system.user.view" -> "system")
                return explode('.', $permission->name)[0] ?? 'other';
            });

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}
