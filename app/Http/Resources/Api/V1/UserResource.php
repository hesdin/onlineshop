<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'profile_image' => $this->getFirstMediaUrl('profile_image') ?: null,
            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'phone' => $this->profile->phone ?? null,
                    'gender' => $this->profile->gender ?? null,
                    'birth_date' => $this->profile->birth_date ?? null,
                ];
            }),
            'roles' => $this->getRoleNames(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
