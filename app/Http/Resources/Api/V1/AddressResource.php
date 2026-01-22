<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'province_id' => $this->province_id,
            'province' => $this->province,
            'city_id' => $this->city_id,
            'city' => $this->city,
            'district_id' => $this->district_id,
            'district' => $this->district,
            'postal_code' => $this->postal_code,
            'address_line' => $this->address_line,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_default' => $this->is_default,
            'note' => $this->note,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
