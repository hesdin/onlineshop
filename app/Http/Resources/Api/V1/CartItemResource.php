<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->subtotal,
            'note' => $this->note,
            'shipping_method' => $this->shipping_method,
            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'price' => $this->product->price,
                'sale_price' => $this->product->sale_price,
                'stock' => $this->product->stock,
                'status' => $this->product->status,
                'image_url' => $this->product->image_url,
                'min_order' => $this->product->min_order,
            ]),
            'store' => $this->whenLoaded('store', fn() => [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
            ]),
        ];
    }
}
