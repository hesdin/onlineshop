<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'type' => $this->type,
            'phone' => $this->phone,
            'is_verified' => $this->is_verified,
            'is_umkm' => $this->is_umkm,
            'rating' => $this->rating,
            'transactions_count' => $this->transactions_count,
            'response_time_label' => $this->response_time_label,
            'location' => [
                'province_id' => $this->province_id,
                'province' => $this->province,
                'city_id' => $this->city_id,
                'city' => $this->city,
                'district_id' => $this->district_id,
                'district' => $this->district,
                'postal_code' => $this->postal_code,
                'address_line' => $this->address_line,
            ],
            'logo_url' => $this->logo_url,
            'banner_url' => $this->banner_url,
            'products_count' => $this->whenCounted('products'),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
