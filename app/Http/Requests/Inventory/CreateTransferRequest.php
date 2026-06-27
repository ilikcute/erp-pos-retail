<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_location_id' => 'required|integer|exists:inventory_locations,id',
            'destination_location_id' => 'required|integer|exists:inventory_locations,id|different:source_location_id',
            'transfer_date' => 'required|date',
            'remarks' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.inventory_batch_id' => 'required|integer|exists:inventory_batches,id',
            'items.*.transfer_qty' => 'required|numeric|min:0.01',
        ];
    }
}
