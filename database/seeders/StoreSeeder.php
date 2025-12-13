<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Services\RegionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        /** @var RegionService $regions */
        $regions = app(RegionService::class);
        $provinceMap = $regions->provinces()->keyBy('name');

        $featuredStores = [
            [
                'name' => 'Batik Nusantara Lestari',
                'tagline' => 'Kain dan busana batik unggulan pengrajin daerah',
                'type' => 'umkm',
                'tax_status' => 'non_pkp',
                'city' => 'KOTA PEKALONGAN',
                'province' => 'JAWA TENGAH',
                'is_verified' => true,
            ],
            [
                'name' => 'Nusantara BUMN Logistik',
                'tagline' => 'Solusi rantai suplai nasional untuk kebutuhan institusi',
                'type' => 'vendor',
                'tax_status' => 'pkp',
                'city' => 'KOTA ADMINISTRASI JAKARTA SELATAN',
                'province' => 'DAERAH KHUSUS IBUKOTA JAKARTA',
                'is_verified' => true,
                'bumn_partner' => 'PT PLN Persero',
            ],
            [
                'name' => 'Koperasi Tani Makmur',
                'tagline' => 'Pangan segar dari petani lokal',
                'type' => 'koperasi',
                'tax_status' => 'non_pkp',
                'city' => 'KABUPATEN MALANG',
                'province' => 'JAWA TIMUR',
                'is_verified' => false,
            ],
        ];

        foreach ($featuredStores as $storeData) {
            $slug = Str::slug($storeData['name']);
            $province = $provinceMap[$storeData['province']] ?? null;
            $city = $province
                ? $regions->cities($province->code)->firstWhere('name', $storeData['city'])
                : $regions->cities()->firstWhere('name', $storeData['city']);
            $district = $city
                ? $regions->districts($city->code)->first()
                : null;

            $payload = array_merge([
                'user_id' => null,
                'description' => $storeData['tagline'],
                'province_id' => $province?->id,
                'city_id' => $city?->id,
                'district_id' => $district?->id,
                'postal_code' => '40111',
                'address_line' => "{$storeData['city']}, {$storeData['province']}",
                'response_time_label' => '24 jam',
                'rating' => 4.5,
                'transactions_count' => 25,
            ], Arr::except($storeData, ['city', 'province']));

            Store::updateOrCreate(['slug' => $slug], $payload);
        }

        $minStores = 15;
        $currentCount = Store::count();
        $remaining = max($minStores - $currentCount, 0);

        if ($remaining > 0) {
            Store::factory()->count($remaining)->create();
        }
    }
}
