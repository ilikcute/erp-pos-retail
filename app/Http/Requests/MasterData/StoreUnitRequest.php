<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('master-data.unit.manage');
    }

    public function rules(): array
    {
        return [
            'unit_code'   => ['required', 'string', 'max:20', 'unique:units,unit_code'],
            'unit_name'   => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }
}
