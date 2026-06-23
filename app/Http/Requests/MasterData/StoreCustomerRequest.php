<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('master-data.customer.create');
    }

    public function rules(): array
    {
        return [
            'customer_code'         => ['required', 'string', 'max:50', 'unique:customers,customer_code'],
            'customer_name'         => ['required', 'string', 'max:150'],
            'customer_category_id'  => ['nullable', 'integer', 'exists:customer_categories,id'],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'email'                 => ['nullable', 'email', 'max:100'],
            'address'               => ['nullable', 'string', 'max:255'],
            'city'                  => ['nullable', 'string', 'max:100'],
            'birth_date'            => ['nullable', 'date'],
            'gender'                => ['nullable', 'in:MALE,FEMALE,OTHER'],
            'tax_id'                => ['nullable', 'string', 'max:30'],
            'credit_limit'          => ['nullable', 'numeric', 'min:0'],
            'is_active'             => ['nullable', 'boolean'],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ];
    }
}
