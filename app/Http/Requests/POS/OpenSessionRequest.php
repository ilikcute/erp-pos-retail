<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class OpenSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.session.open');
    }

    public function rules(): array
    {
        return [
            'shift_id'     => 'required|exists:shifts,id',
            'opening_cash' => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:500',
        ];
    }
}
