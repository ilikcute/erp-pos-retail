<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class CloseDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Jika user memiliki hak akses langsung (admin, supervisor, manager, owner)
        if ($user->hasAnyRole(['admin', 'supervisor', 'manager', 'owner']) || $user->hasPermission('pos.day-closing.manage')) {
            return true;
        }

        // Jika kasir biasa, mereka wajib menyertakan kredensial supervisor di payload
        return $this->has('supervisor_email') && $this->has('supervisor_password');
    }

    public function rules(): array
    {
        return [
            'closing_date' => 'required|date|before_or_equal:today',
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
            'closing_date.required' => 'Tanggal tutup wajib diisi',
            'closing_date.before_or_equal' => 'Tanggal tidak boleh di masa depan',
            'supervisor_email.exists' => 'Email supervisor tidak ditemukan',
        ];
    }
}
