<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class RegionService
{
    public function provinces(): Collection
    {
        return $this->rememberCollection(
            'regions:provinces',
            fn () => Province::query()
                ->select(['id', 'code', 'name'])
                ->orderBy('name')
                ->get()
        );
    }

    public function cities(?string $provinceCode = null): Collection
    {
        $cacheKey = $provinceCode ? "regions:cities:{$provinceCode}" : 'regions:cities:all';

        return $this->rememberCollection(
            $cacheKey,
            function () use ($provinceCode) {
                $query = City::query()
                    ->select(['id', 'code', 'name', 'province_code'])
                    ->orderBy('name');

                if ($provinceCode) {
                    $query->where('province_code', $provinceCode);
                }

                return $query->get();
            }
        );
    }

    public function districts(?string $cityCode = null): Collection
    {
        $cacheKey = $cityCode ? "regions:districts:{$cityCode}" : 'regions:districts:all';

        return $this->rememberCollection(
            $cacheKey,
            function () use ($cityCode) {
                $query = District::query()
                    ->select(['id', 'code', 'name', 'city_code'])
                    ->orderBy('name');

                if ($cityCode) {
                    $query->where('city_code', $cityCode);
                }

                return $query->get();
            }
        );
    }

    public function provinceName(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        return $this->provinces()->firstWhere('code', $code)?->name;
    }

    public function cityName(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        return $this->cities()->firstWhere('code', $code)?->name;
    }

    public function districtName(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        return $this->districts()->firstWhere('code', $code)?->name;
    }

    /**
     * Cache region collections while allowing refresh when data was cached before seeding.
     */
    private function rememberCollection(string $cacheKey, Closure $resolver): Collection
    {
        $cached = Cache::get($cacheKey);

        if ($cached instanceof Collection && $cached->isNotEmpty()) {
            return $cached;
        }

        $fresh = $resolver();

        if ($fresh->isNotEmpty()) {
            Cache::put($cacheKey, $fresh, now()->addDay());
        } else {
            Cache::forget($cacheKey);
        }

        return $fresh;
    }
}
