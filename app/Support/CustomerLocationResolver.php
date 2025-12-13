<?php

namespace App\Support;

use App\Models\Address;
use Illuminate\Http\Request;

class CustomerLocationResolver
{
    public static function resolveCityId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return Address::query()
            ->where('user_id', $user->getKey())
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->value('city_id');
    }

    public static function resolveProvinceId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return Address::query()
            ->where('user_id', $user->getKey())
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->value('province_id');
    }
}
