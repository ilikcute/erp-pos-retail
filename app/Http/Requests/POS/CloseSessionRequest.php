<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class CloseSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.session.close');
    }

    public function rules(): array
    {
        return [
            'closing_cash' => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:500',
        ];
    }
}
