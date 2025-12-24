<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use App\Services\RegionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        /** @var RegionService $regions */
        $regions = app(RegionService::class);
        $provinceMap = $regions->provinces()->keyBy('name');

        // Get the seller user
        $seller = User::role('seller')->first();

        if (!$seller) {
            $this->command?->warn('StoreSeeder: tidak ada user dengan role seller. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $province = $provinceMap['DAERAH KHUSUS IBUKOTA JAKARTA'] ?? $regions->provinces()->first();
        $city = $province
            ? $regions->cities($province->code)->firstWhere('name', 'KOTA ADMINISTRASI JAKARTA SELATAN')
            : null;
        $district = $city
            ? $regions->districts($city->code)->first()
            : null;

        // Create 1 store for electronic products
        Store::updateOrCreate(
            ['slug' => 'toko-elektronik-jaya'],
            [
                'user_id' => $seller->id,
                'name' => 'Toko Elektronik Jaya',
                'tagline' => 'Pusat Elektronik Terlengkap',
                'description' => 'Toko elektronik dengan produk berkualitas dan harga terjangkau.',
                'type' => 'umkm',
                'tax_status' => 'non_pkp',
                'province_id' => $province?->id,
                'city_id' => $city?->id,
                'district_id' => $district?->id,
                'postal_code' => '12210',
                'address_line' => $city && $province ? "{$city->name}, {$province->name}" : 'Jl. Elektronik No. 1',
                'is_verified' => true,
                'is_umkm' => true,
                'rating' => 4.8,
                'transactions_count' => 150,
                'response_time_label' => '24 jam',
            ]
        );

        $this->command?->info('StoreSeeder: 1 toko elektronik berhasil dibuat.');
    }
}
