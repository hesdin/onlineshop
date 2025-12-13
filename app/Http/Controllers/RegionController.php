<?php

namespace App\Http\Controllers;

use App\Services\RegionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function provinces(RegionService $regions): JsonResponse
    {
        $data = $regions->provinces()
            ->map(fn ($province) => [
                'id' => $province->id,
                'code' => $province->code,
                'name' => $province->name,
            ])
            ->values();

        return response()->json($data);
    }

    public function cities(Request $request, RegionService $regions): JsonResponse
    {
        $provinceCode = $request->string('province_code')->toString();

        $data = $regions->cities($provinceCode ?: null)
            ->map(fn ($city) => [
                'id' => $city->id,
                'code' => $city->code,
                'name' => $city->name,
                'province_code' => $city->province_code,
            ])
            ->values();

        return response()->json($data);
    }

    public function districts(Request $request, RegionService $regions): JsonResponse
    {
        $cityCode = $request->string('city_code')->toString();

        $data = $regions->districts($cityCode ?: null)
            ->map(fn ($district) => [
                'id' => $district->id,
                'code' => $district->code,
                'name' => $district->name,
                'city_code' => $district->city_code,
            ])
            ->values();

        return response()->json($data);
    }
}
