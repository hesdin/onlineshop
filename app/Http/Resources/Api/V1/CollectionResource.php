<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'color_theme' => $this->color_theme,
            'display_mode' => $this->display_mode,
            'is_active' => $this->is_active,
            'home_image_url' => $this->getFirstMediaUrl('home_image') ?: null,
            'cover_image_url' => $this->getFirstMediaUrl('cover_image') ?: null,
            'products' => ProductListResource::collection($this->whenLoaded('products')),
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
