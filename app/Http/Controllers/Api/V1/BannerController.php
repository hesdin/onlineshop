<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Get active banners
     */
    public function index(Request $request): JsonResponse
    {
        $query = Banner::active()->ordered();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $banners = $query->get();

        return response()->json([
            'banners' => BannerResource::collection($banners),
        ]);
    }
}
