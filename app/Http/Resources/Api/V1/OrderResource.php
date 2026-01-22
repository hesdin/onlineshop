<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'purchase_request_number' => $this->purchase_request_number,
            'po_number' => $this->po_number,
            'order_type' => $this->order_type,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_term' => $this->payment_term,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'shipping_cost' => $this->shipping_cost,
            'weight_total' => $this->weight_total,
            'grand_total' => $this->grand_total,
            'shipping' => [
                'service' => $this->shipping_service,
                'awb' => $this->shipping_awb,
                'eta' => $this->shipping_eta,
            ],
            'note' => $this->note,
            'ordered_at' => $this->ordered_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'store' => $this->whenLoaded('store', fn() => new StoreResource($this->store)),
            'address' => $this->whenLoaded('address', fn() => new AddressResource($this->address)),
            'payment_method' => $this->whenLoaded('paymentMethod', fn() => [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
                'code' => $this->paymentMethod->code,
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment_proof_url' => $this->payment_proof_url,
            'whatsapp_link' => $this->getWhatsAppLink(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
