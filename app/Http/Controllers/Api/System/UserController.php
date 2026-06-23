<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreUserRequest;
use App\Http\Requests\System\UpdateUserRequest;
use App\Http\Resources\System\UserResource;
use App\Services\System\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('system.user.view');

        $users = $this->userService->paginate(
            filters: $request->only(['search', 'status', 'role_id']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('system.user.view');

        $user = $this->userService->findById($id);

        abort_if(! $user, 404, 'User tidak ditemukan.');

        return response()->json([
            'data' => new UserResource($user->load('roles.permissions')),
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json([
            'data'    => new UserResource($user),
            'message' => 'User berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        abort_if(! $user, 404, 'User tidak ditemukan.');

        $user = $this->userService->update($user, $request->validated());

        return response()->json([
            'data'    => new UserResource($user),
            'message' => 'User berhasil diperbarui.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('system.user.manage');

        $user = $this->userService->findById($id);

        abort_if(! $user, 404, 'User tidak ditemukan.');
        abort_if($user->id === auth()->id(), 422, 'Tidak dapat menghapus akun sendiri.');

        $this->userService->delete($user);

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
