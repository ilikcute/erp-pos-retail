<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class VoidSalesTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.transaction.void');
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500',
        ];
    }
}
