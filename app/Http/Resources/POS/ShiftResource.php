<?php

namespace App\Http\Resources\POS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'shift_code'  => $this->shift_code,
            'shift_name'  => $this->shift_name,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'is_active'   => $this->is_active,
            'description' => $this->description,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
