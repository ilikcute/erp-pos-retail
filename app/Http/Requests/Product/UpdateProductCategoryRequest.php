<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('product.category.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('id');

        return [
            // 'code' dihapus karena auto-generate
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:product_categories,id',
                // Validasi: parent tidak boleh dirinya sendiri (mencegah circular reference)
                Rule::notIn([$categoryId]),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'parent_id.exists' => 'Kategori induk tidak ditemukan.',
            'parent_id.not_in' => 'Kategori tidak boleh menjadi parent dari dirinya sendiri.',
        ];
    }
}
