<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $faker = $this->faker ?? fake();
        $name = $faker->unique()->words(3, true);
        $price = $faker->numberBetween(10000, 500000);
        $salePrice = $faker->boolean(40) ? $faker->numberBetween(5000, $price) : null;
        $itemTypes = collect(Product::itemTypes())->pluck('value')->toArray();
        $statuses = collect(Product::statuses())->pluck('value')->toArray();
        $province = Province::inRandomOrder()->first();
        $city = $province
            ? City::where('province_code', $province->code)->inRandomOrder()->first()
            : City::inRandomOrder()->first();
        $district = $city
            ? District::where('city_code', $city->code)->inRandomOrder()->first()
            : null;

        return [
            'store_id' => Store::factory(),
            'category_id' => Category::inRandomOrder()->value('id') ?? null,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $faker->unique()->numberBetween(1, 9999),
            'brand' => $faker->company,
            'description' => $faker->paragraph,
            'price' => $price,
            'sale_price' => $salePrice,
            'min_order' => $faker->numberBetween(1, 5),
            'stock' => $faker->numberBetween(0, 200),
            'weight' => $faker->numberBetween(100, 5000),
            'length' => $faker->randomFloat(2, 10, 120),
            'width' => $faker->randomFloat(2, 10, 80),
            'height' => $faker->randomFloat(2, 2, 60),
            'item_type' => $faker->randomElement($itemTypes ?: ['product']),
            'status' => $faker->randomElement($statuses ?: ['ready']),
            'visibility_scope' => Product::VISIBILITY_GLOBAL,
            'location_province_id' => $province?->id,
            'location_city_id' => $city?->id,
            'location_district_id' => $district?->id,
            'location_postal_code' => $faker->postcode,
        ];
    }
}
