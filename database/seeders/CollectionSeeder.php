<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'title' => 'Promo Flash Sale',
                'description' => 'Produk dengan diskon terbatas',
                'is_active' => true,
            ],
            [
                'title' => 'Best Seller',
                'description' => 'Produk terlaris bulan ini',
                'is_active' => true,
            ],
            [
                'title' => 'New Arrivals',
                'description' => 'Produk terbaru',
                'is_active' => true,
            ],
        ];

        foreach ($collections as $collectionData) {
            $slug = Str::slug($collectionData['title']);
            Collection::updateOrCreate(
                ['slug' => $slug],
                $collectionData
            );
        }
    }
}
