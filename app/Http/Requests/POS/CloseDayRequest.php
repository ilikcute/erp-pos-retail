<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class CloseDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'closing_date' => 'required|date|before_or_equal:today',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'closing_date.required' => 'Tanggal tutup wajib diisi',
            'closing_date.before_or_equal' => 'Tanggal tidak boleh di masa depan',
        ];
    }
}
