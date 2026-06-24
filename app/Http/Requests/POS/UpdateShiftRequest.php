<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.shift.update');
    }

    public function rules(): array
    {
        return [
            'shift_name'  => 'sometimes|required|string|max:100',
            'start_time'  => 'sometimes|required|date_format:H:i',
            'end_time'    => 'sometimes|required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ];
    }
}
