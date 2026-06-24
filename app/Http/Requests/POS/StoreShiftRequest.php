<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.shift.create');
    }

    public function rules(): array
    {
        return [
            'shift_code'  => 'required|string|max:50|unique:shifts,shift_code',
            'shift_name'  => 'required|string|max:100',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ];
    }
}
