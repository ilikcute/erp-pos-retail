<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('master-data.tax.manage');
    }

    public function rules(): array
    {
        $taxId = $this->route('tax');

        return [
            'tax_code' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('taxes', 'tax_code')->ignore($taxId)],
            'tax_name' => ['sometimes', 'required', 'string', 'max:100'],
            'tax_rate' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'is_inclusive' => ['sometimes', 'required', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
