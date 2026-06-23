<?php

namespace App\Http\Resources\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tax_code'     => $this->tax_code,
            'tax_name'     => $this->tax_name,
            'tax_rate'     => $this->tax_rate,
            'is_inclusive' => $this->is_inclusive,
            'is_active'    => $this->is_active,
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}
