<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('system.user.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'in:ACTIVE,INACTIVE'],
            'force_password_change' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array', 'max:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
