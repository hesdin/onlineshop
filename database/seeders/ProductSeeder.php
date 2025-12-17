<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();
        $categories = Category::all();

        if ($stores->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Stores or Categories are empty. Please run StoreSeeder and CategorySeeder first.');
            return;
        }

        $products = [
            [
                'name' => 'Kopi Arabika Gayo 250gr',
                'brand' => 'Rincon Kopi',
                'description' => 'Biji kopi single origin Gayo, medium roast, kemasan 250gr.',
                'price' => 95000,
                'stock' => 60,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
            ],
            [
                'name' => 'Batik Tulis Pekalongan',
                'brand' => 'Batik Lestari',
                'description' => 'Kain batik tulis motif klasik, bahan katun primissima.',
                'price' => 425000,
                'stock' => 25,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
            ],
            [
                'name' => 'Keripik Pisang Lampung 500gr',
                'brand' => 'Cemilan Nusantara',
                'description' => 'Keripik pisang manis gurih khas Lampung, kemasan 500gr.',
                'price' => 38000,
                'stock' => 120,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
            ],
            [
                'name' => 'Madu Hutan Sumbawa 500ml',
                'brand' => 'Madu Rimba',
                'description' => 'Madu hutan murni dari Sumbawa, kemasan botol kaca 500ml.',
                'price' => 185000,
                'stock' => 40,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
            ],
            [
                'name' => 'Sambal Roa Manado 200gr',
                'brand' => 'Rumah Dapur Minahasa',
                'description' => 'Sambal roa khas Manado, pedas gurih, siap saji.',
                'price' => 52000,
                'stock' => 80,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
            ],
        ];

        foreach ($products as $productData) {
            $slug = Str::slug($productData['name']);
            Product::updateOrCreate(
                ['slug' => $slug],
                array_merge([
                    'store_id' => $stores->random()->id,
                    'category_id' => $categories->random()->id,
                    'min_order' => 1,
                    'weight' => 1000,
                    'visibility_scope' => Product::VISIBILITY_GLOBAL,
                ], $productData)
            );
        }
    }
}
