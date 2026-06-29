<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class CloseMonthRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Jika user memiliki hak akses langsung (admin, supervisor, manager, owner)
        if ($user->hasAnyRole(['admin', 'supervisor', 'manager', 'owner']) || $user->hasPermission('pos.month-closing.manage')) {
            return true;
        }

        // Jika kasir biasa, mereka wajib menyertakan kredensial supervisor di payload
        return $this->has('supervisor_email') && $this->has('supervisor_password');
    }

    public function rules(): array
    {
        return [
            'closing_year' => 'required|integer|min:2000|max:2100',
            'closing_month' => 'required|integer|min:1|max:12',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
            'notes' => 'nullable|string|max:1000',
            // Kredensial supervisor jika kasir yang melakukan request
            'supervisor_email' => 'nullable|email|exists:users,email',
            'supervisor_password' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'closing_year.required' => 'Tahun wajib diisi',
            'closing_month.required' => 'Bulan wajib diisi',
            'closing_month.min' => 'Bulan harus antara 1-12',
            'closing_month.max' => 'Bulan harus antara 1-12',
            'supervisor_email.exists' => 'Email supervisor tidak ditemukan',
        ];
    }
}
