<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('master-data.supplier.update');
    }

    public function rules(): array
    {
        $supplierId = $this->route('supplier') ?? $this->route('id');

        return [
            'supplier_code'     => ['sometimes', 'string', 'max:50', Rule::unique('suppliers', 'supplier_code')->ignore($supplierId)],
            'supplier_name'     => ['sometimes', 'string', 'max:150'],
            'contact_person'    => ['nullable', 'string', 'max:100'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'email'             => ['nullable', 'email', 'max:100'],
            'address'           => ['nullable', 'string', 'max:255'],
            'city'              => ['nullable', 'string', 'max:100'],
            'province'          => ['nullable', 'string', 'max:100'],
            'postal_code'       => ['nullable', 'string', 'max:10'],
            'tax_id'            => ['nullable', 'string', 'max:30'],
            'payment_term_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'credit_limit'      => ['nullable', 'numeric', 'min:0'],
            'is_active'         => ['nullable', 'boolean'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];
    }
}
