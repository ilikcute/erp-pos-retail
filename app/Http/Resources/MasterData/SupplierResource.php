<?php

namespace App\Http\Resources\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'supplier_code'     => $this->supplier_code,
            'supplier_name'     => $this->supplier_name,
            'contact_person'    => $this->contact_person,
            'phone'             => $this->phone,
            'email'             => $this->email,
            'address'           => $this->address,
            'city'              => $this->city,
            'province'          => $this->province,
            'postal_code'       => $this->postal_code,
            'tax_id'            => $this->tax_id,
            'payment_term_days' => $this->payment_term_days,
            'credit_limit'      => $this->credit_limit,
            'is_active'         => $this->is_active,
            'notes'             => $this->notes,
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
