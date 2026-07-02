<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('master-data.tax.manage');
    }

    public function rules(): array
    {
        return [
            'tax_code' => ['required', 'string', 'max:20', 'unique:taxes,tax_code'],
            'tax_name' => ['required', 'string', 'max:100'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_inclusive' => ['required', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
