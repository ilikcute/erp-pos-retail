<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('master-data.unit.manage');
    }

    public function rules(): array
    {
        $unitId = $this->route('unit');

        return [
            'unit_code'   => ['sometimes', 'required', 'string', 'max:20', Rule::unique('units', 'unit_code')->ignore($unitId)],
            'unit_name'   => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }
}
