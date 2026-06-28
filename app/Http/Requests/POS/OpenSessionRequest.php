<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class OpenSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_id' => 'required|integer|exists:cashier_shifts,id',
            'opening_cash' => 'required|numeric|min:0',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'shift_id.required' => 'Shift wajib dipilih',
            'shift_id.exists' => 'Shift tidak ditemukan',
            'opening_cash.required' => 'Modal awal wajib diisi',
            'opening_cash.min' => 'Modal awal tidak boleh negatif',
        ];
    }
}
