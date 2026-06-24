<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('product.brand.manage');
    }

    public function rules(): array
    {
        $brandId = $this->route('id');

        return [
            'code'        => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_brands', 'brand_code')->ignore($brandId),
            ],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ];
    }
}
