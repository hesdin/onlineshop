<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->brand,
            'description' => $this->description,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'effective_price' => $this->sale_price ?? $this->price,
            'discount_percentage' => $this->sale_price
                ? round((($this->price - $this->sale_price) / $this->price) * 100)
                : null,
            'min_order' => $this->min_order,
            'stock' => $this->stock,
            'weight' => $this->weight,
            'dimensions' => [
                'length' => $this->length,
                'width' => $this->width,
                'height' => $this->height,
            ],
            'item_type' => $this->item_type,
            'status' => $this->status,
            'visibility_scope' => $this->visibility_scope,
            'is_pdn' => $this->is_pdn,
            'shipping' => [
                'pickup' => $this->shipping_pickup,
                'delivery' => $this->shipping_delivery,
            ],
            'location' => [
                'province_id' => $this->location_province_id,
                'province' => $this->location_province,
                'city_id' => $this->location_city_id,
                'city' => $this->location_city,
                'district_id' => $this->location_district_id,
                'district' => $this->location_district,
                'postal_code' => $this->location_postal_code,
            ],
            'image_url' => $this->image_url,
            'images' => $this->getMedia('product_image')->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
            ]),
            'store' => $this->whenLoaded('store', fn() => new StoreResource($this->store)),
            'category' => $this->whenLoaded('category', fn() => new CategoryResource($this->category)),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
