<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('product.brand.manage');
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:50', 'unique:product_brands,brand_code'],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ];
    }
}
