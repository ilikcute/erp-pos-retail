<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\Auth\UserProfileResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            email: $request->email,
            password: $request->password,
            deviceName: $request->input('device_name', 'web'),
        );

        return response()->json([
            'data' => [
                'user'  => new UserProfileResource($result['user']),
                'token' => $result['token'],
            ],
            'message' => 'Login berhasil.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function logoutAllDevices(Request $request): JsonResponse
    {
        $this->authService->logoutAllDevices($request->user());

        return response()->json(['message' => 'Logout dari semua perangkat berhasil.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserProfileResource($request->user()->load('roles.permissions')),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            user: $request->user(),
            currentPassword: $request->current_password,
            newPassword: $request->new_password,
        );

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}
