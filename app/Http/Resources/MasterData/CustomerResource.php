<?php

namespace App\Http\Resources\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_code' => $this->customer_code,
            'customer_name' => $this->customer_name,
            'customer_category_id' => $this->customer_category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'category_name' => $this->category->category_name,
            ]),
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'birth_date' => $this->birth_date?->toDateString(),
            'gender' => $this->gender,
            'tax_id' => $this->tax_id,
            'credit_limit' => $this->credit_limit,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
