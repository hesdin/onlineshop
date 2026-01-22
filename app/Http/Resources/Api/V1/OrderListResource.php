<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'grand_total' => $this->grand_total,
            'items_count' => $this->items->count(),
            'first_item' => $this->items->first() ? [
                'name' => $this->items->first()->name,
                'image_url' => $this->items->first()->product?->image_url,
            ] : null,
            'store' => $this->whenLoaded('store', fn() => [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
                'logo_url' => $this->store->logo_url,
            ]),
            'ordered_at' => $this->ordered_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
