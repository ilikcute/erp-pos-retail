<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('system.user.manage');
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', Password::min(8)->letters()->numbers()],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'in:ACTIVE,INACTIVE,SUSPENDED'],
            'force_password_change' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array', 'max:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
