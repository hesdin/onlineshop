<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class RegionController extends Controller
{
    /**
     * Get provinces list
     */
    public function provinces(): JsonResponse
    {
        $provinces = Province::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'provinces' => $provinces,
        ]);
    }

    /**
     * Get cities by province
     */
    public function cities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'exists:indonesia_provinces,id'],
        ]);

        $cities = City::where('province_id', $validated['province_id'])
            ->orderBy('name')
            ->get(['id', 'name', 'province_id']);

        return response()->json([
            'cities' => $cities,
        ]);
    }

    /**
     * Get districts by city
     */
    public function districts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city_id' => ['required', 'exists:indonesia_cities,id'],
        ]);

        $districts = District::where('city_id', $validated['city_id'])
            ->orderBy('name')
            ->get(['id', 'name', 'city_id']);

        return response()->json([
            'districts' => $districts,
        ]);
    }
}
