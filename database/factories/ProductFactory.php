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
        $name = $this->faker->unique()->words(3, true);
        $price = $this->faker->numberBetween(10000, 500000);
        $salePrice = $this->faker->boolean(40) ? $this->faker->numberBetween(5000, $price) : null;
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
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'brand' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'price' => $price,
            'sale_price' => $salePrice,
            'min_order' => $this->faker->numberBetween(1, 5),
            'stock' => $this->faker->numberBetween(0, 200),
            'weight' => $this->faker->numberBetween(100, 5000),
            'length' => $this->faker->randomFloat(2, 10, 120),
            'width' => $this->faker->randomFloat(2, 10, 80),
            'height' => $this->faker->randomFloat(2, 2, 60),
            'item_type' => $this->faker->randomElement($itemTypes ?: ['product']),
            'status' => $this->faker->randomElement($statuses ?: ['ready']),
            'visibility_scope' => Product::VISIBILITY_GLOBAL,
            'location_province_id' => $province?->id,
            'location_city_id' => $city?->id,
            'location_district_id' => $district?->id,
            'location_postal_code' => $this->faker->postcode,
            'is_pdn' => $this->faker->boolean(50),
            'is_pkp' => $this->faker->boolean(40),
            'is_tkdn' => $this->faker->boolean(30),
        ];
    }
}
