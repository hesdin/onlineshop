<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AddressController extends Controller
{
    /**
     * List user addresses
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->with(['provinceRegion', 'cityRegion', 'districtRegion'])
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return AddressResource::collection($addresses);
    }

    /**
     * Create new address
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'province_id' => ['required', 'exists:indonesia_provinces,id'],
            'city_id' => ['required', 'exists:indonesia_cities,id'],
            'district_id' => ['required', 'exists:indonesia_districts,id'],
            'postal_code' => ['required', 'string', 'max:10'],
            'address_line' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ]);

        $validated['user_id'] = $request->user()->id;

        // If this is the first address or set as default
        if ($validated['is_default'] ?? false) {
            Address::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        // If no address exists, make this the default
        $hasAddresses = Address::where('user_id', $request->user()->id)->exists();
        if (!$hasAddresses) {
            $validated['is_default'] = true;
        }

        $address = Address::create($validated);
        $address->load(['provinceRegion', 'cityRegion', 'districtRegion']);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'address' => new AddressResource($address),
        ], 201);
    }

    /**
     * Update address
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        // Ensure address belongs to user
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'label' => ['sometimes', 'string', 'max:50'],
            'recipient_name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'province_id' => ['sometimes', 'exists:indonesia_provinces,id'],
            'city_id' => ['sometimes', 'exists:indonesia_cities,id'],
            'district_id' => ['sometimes', 'exists:indonesia_districts,id'],
            'postal_code' => ['sometimes', 'string', 'max:10'],
            'address_line' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $address->update($validated);
        $address->load(['provinceRegion', 'cityRegion', 'districtRegion']);

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
            'address' => new AddressResource($address),
        ]);
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        // Ensure address belongs to user
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set another as default
        if ($wasDefault) {
            $newDefault = Address::where('user_id', $request->user()->id)
                ->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }

    /**
     * Set address as default
     */
    public function setDefault(Request $request, Address $address): JsonResponse
    {
        // Ensure address belongs to user
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        // Unset other defaults
        Address::where('user_id', $request->user()->id)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return response()->json([
            'message' => 'Alamat berhasil dijadikan alamat utama.',
            'address' => new AddressResource($address->fresh()),
        ]);
    }
}
