<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition()
    {
        $name = $this->faker->unique()->company;
        $slug = Str::slug($name);
        $province = Province::inRandomOrder()->first();
        $city = $province
            ? City::where('province_code', $province->code)->inRandomOrder()->first()
            : City::inRandomOrder()->first();
        $district = $city
            ? District::where('city_code', $city->code)->inRandomOrder()->first()
            : null;

        return [
            'user_id' => null,
            'name' => $name,
            'slug' => "{$slug}-{$this->faker->unique()->numberBetween(1, 9999)}",
            'tagline' => $this->faker->catchPhrase,
            'description' => $this->faker->paragraph,
            'type' => $this->faker->randomElement(['umkm', 'vendor', 'koperasi', 'premium']),
            'tax_status' => $this->faker->randomElement(['pkp', 'non_pkp']),
            'bumn_partner' => $this->faker->boolean(30) ? $this->faker->company : null,
            'province_id' => $province?->id,
            'city_id' => $city?->id,
            'district_id' => $district?->id,
            'postal_code' => $this->faker->postcode,
            'address_line' => $this->faker->address,
            'is_verified' => $this->faker->boolean(60),
            'is_umkm' => $this->faker->boolean(80),
            'rating' => $this->faker->randomFloat(2, 3.0, 5.0),
            'transactions_count' => $this->faker->numberBetween(0, 250),
            'response_time_label' => $this->faker->randomElement(['24 jam', '48 jam', '3 hari']),
        ];
    }
}
