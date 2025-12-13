<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Services\RegionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class StoreProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = app(RegionService::class);
        $province = $regions->provinces()->firstWhere('name', 'JAWA TENGAH') ?? $regions->provinces()->first();
        $city = $province
            ? $regions->cities($province->code)->firstWhere('name', 'KOTA PEKALONGAN')
            : $regions->cities()->first();
        $district = $city
            ? $regions->districts($city->code)->first()
            : null;

        $categoryIds = Category::pluck('id')->all();

        if (empty($categoryIds)) {
            $this->command?->warn('StoreProductSeeder skipped because no categories exist.');
            return;
        }

        $stores = Store::all();

        if ($stores->isEmpty()) {
            $stores = Store::factory()->count(5)->create([
                'province_id' => $province?->id,
                'city_id' => $city?->id,
                'district_id' => $district?->id,
                'postal_code' => '51121',
            ]);
        }

        $stores->each(function (Store $store) use ($categoryIds) {
            Product::factory()
                ->count(3)
                ->for($store)
                ->state(fn () => [
                    'category_id' => Arr::random($categoryIds),
                ])
                ->create();
        });
    }
}
