<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items->load(['product.store', 'store']);

        // Group items by store
        $groupedByStore = $items->groupBy('store_id')->map(function ($storeItems, $storeId) {
            $store = $storeItems->first()->store;
            $storeSubtotal = $storeItems->sum('subtotal');

            return [
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'city' => $store->city,
                    'logo_url' => $store->logo_url,
                ],
                'items' => CartItemResource::collection($storeItems),
                'subtotal' => $storeSubtotal,
            ];
        })->values();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'stores' => $groupedByStore,
            'summary' => [
                'total_items' => $items->sum('quantity'),
                'total_products' => $items->count(),
                'subtotal' => $items->sum('subtotal'),
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
