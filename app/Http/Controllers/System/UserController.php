<?php

namespace App\Http\Controllers\System;

use App\Actions\System\CreateUserAction;
use App\Actions\System\DeleteUserAction;
use App\Actions\System\UpdateUserAction;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreUserRequest;
use App\Http\Requests\System\UpdateUserRequest;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository
    ) {}

    public function index(): Response
    {
        $users = $this->userRepository->paginate(request()->only('search'), 15);
        $roles = $this->roleRepository->listAll();

        return Inertia::render('System/Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('system.users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(int $id, UpdateUserRequest $request, UpdateUserAction $action): RedirectResponse
    {
        $user = $this->userRepository->findById($id);
        if (! $user) {
            abort(404);
        }

        $action->execute($user, $request->validated());

        return redirect()->route('system.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(int $id, DeleteUserAction $action): RedirectResponse
    {
        $user = $this->userRepository->findById($id);
        if (! $user) {
            abort(404);
        }

        try {
            $action->execute($user);

            return redirect()->route('system.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (BusinessException $e) {
            return redirect()->route('system.users.index')
                ->withErrors(['delete' => $e->getMessage()]);
        }
    }
}
