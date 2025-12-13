<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\RegionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $regions = app(RegionService::class);
        $defaultProvince = $regions->provinces()->firstWhere('code', '31') ?? $regions->provinces()->first();
        $defaultCity = $defaultProvince
            ? $regions->cities($defaultProvince->code)->firstWhere('name', 'KOTA ADMINISTRASI JAKARTA SELATAN')
            : null;
        $defaultDistrict = $defaultCity
            ? $regions->districts($defaultCity->code)->first()
            : null;

        $sellers = User::role('seller')->get();

        if ($sellers->isEmpty()) {
            $this->command?->warn('SellerSeeder: tidak ada user dengan role seller. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $categoryIds = Category::pluck('id')->all();

        if (empty($categoryIds)) {
            $this->command?->warn('SellerSeeder: kategori kosong, jalankan CategorySeeder dulu.');
            return;
        }

        $unassignedStores = Store::whereNull('user_id')->get();

        foreach ($sellers as $seller) {
            $store = Store::where('user_id', $seller->id)->first();

            if (! $store && $unassignedStores->isNotEmpty()) {
                $store = $unassignedStores->shift();
            }

            if (! $store) {
                $storeName = "{$seller->name} Store";
                $store = Store::create([
                    'user_id' => $seller->id,
                    'name' => $storeName,
                    'slug' => Str::slug($storeName) . '-' . $seller->id,
                    'tagline' => 'Toko resmi seller',
                    'description' => 'Seeder toko untuk pengujian panel seller.',
                    'type' => 'umkm',
                    'tax_status' => 'non_pkp',
                    'province_id' => $defaultProvince?->id,
                    'city_id' => $defaultCity?->id,
                    'district_id' => $defaultDistrict?->id,
                    'postal_code' => '12210',
                    'address_line' => $defaultCity && $defaultProvince ? "{$defaultCity->name}, {$defaultProvince->name}" : 'Jl. Contoh No. 1',
                    'is_verified' => true,
                    'is_umkm' => true,
                    'rating' => 4.5,
                    'transactions_count' => 10,
                    'response_time_label' => '24 jam',
                ]);
            } else {
                $store->update([
                    'user_id' => $seller->id,
                    'is_verified' => $store->is_verified ?? true,
                    'is_umkm' => $store->is_umkm ?? true,
                    'response_time_label' => $store->response_time_label ?? '24 jam',
                ]);
            }

            $existingProducts = $store->products()->count();
            $needed = max(0, 3 - $existingProducts);

            if ($needed > 0) {
                Product::factory()
                    ->count($needed)
                    ->for($store)
                    ->state(fn () => ['category_id' => Arr::random($categoryIds)])
                    ->create();
            }
        }

        $this->command?->info('SellerSeeder: seller, toko, dan produk contoh siap untuk pengujian.');
    }
}
