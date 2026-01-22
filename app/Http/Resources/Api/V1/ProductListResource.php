<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->brand,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'effective_price' => $this->sale_price ?? $this->price,
            'discount_percentage' => $this->sale_price
                ? round((($this->price - $this->sale_price) / $this->price) * 100)
                : null,
            'stock' => $this->stock,
            'status' => $this->status,
            'image_url' => $this->image_url,
            'store' => $this->whenLoaded('store', fn() => [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
                'city' => $this->store->city,
                'is_verified' => $this->store->is_verified,
            ]),
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
        ];
    }
}
