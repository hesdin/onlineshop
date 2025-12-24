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
        $store = Store::where('slug', 'toko-elektronik-jaya')->first();

        // Get electronics category
        $elektronikCategory = Category::where('slug', 'elektronik')->first();
        $laptopCategory = Category::where('slug', 'laptop-aksesoris')->first();
        $smartphoneCategory = Category::where('slug', 'smartphone-tablet')->first();

        if (!$store) {
            $this->command?->warn('Store "Toko Elektronik Jaya" tidak ditemukan. Jalankan StoreSeeder terlebih dahulu.');
            return;
        }

        if (!$elektronikCategory) {
            $this->command?->warn('Kategori Elektronik tidak ditemukan. Jalankan CategorySeeder terlebih dahulu.');
            return;
        }

        // 5 Produk Elektronik Indonesia
        $products = [
            [
                'name' => 'Laptop Axioo Hype 5 AMD Ryzen',
                'brand' => 'Axioo',
                'description' => 'Laptop Axioo Hype 5 dengan prosesor AMD Ryzen 5, RAM 8GB, SSD 512GB, layar 14 inch Full HD. Produk laptop asli Indonesia dengan garansi resmi 2 tahun.',
                'price' => 6999000,
                'sale_price' => 6499000,
                'stock' => 30,
                'weight' => 1600,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
                'category_id' => $laptopCategory?->id ?? $elektronikCategory->id,
            ],
            [
                'name' => 'Smartphone Advan G9 Pro 5G',
                'brand' => 'Advan',
                'description' => 'Smartphone Advan G9 Pro 5G buatan Indonesia, RAM 8GB, Storage 128GB, Kamera 64MP, Layar AMOLED 6.5 inch, Baterai 5000mAh. Mendukung jaringan 5G Indonesia.',
                'price' => 3299000,
                'sale_price' => 2999000,
                'stock' => 50,
                'weight' => 195,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
                'category_id' => $smartphoneCategory?->id ?? $elektronikCategory->id,
            ],
            [
                'name' => 'TWS Earbuds Simbadda CST 906N',
                'brand' => 'Simbadda',
                'description' => 'True Wireless Stereo Earbuds Simbadda CST 906N dengan Active Noise Cancelling, Bluetooth 5.2, baterai 6 jam playtime, IPX4 Water Resistant. Produk audio Indonesia.',
                'price' => 399000,
                'sale_price' => 349000,
                'stock' => 100,
                'weight' => 45,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
                'category_id' => $elektronikCategory->id,
            ],
            [
                'name' => 'Smart TV Polytron LED 43 Inch Android',
                'brand' => 'Polytron',
                'description' => 'Smart TV LED Polytron 43 inch dengan Android TV, resolusi Full HD 1080p, Dolby Audio, Chromecast Built-in. Produk elektronik kebanggaan Indonesia.',
                'price' => 3999000,
                'sale_price' => null,
                'stock' => 20,
                'weight' => 8500,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
                'category_id' => $elektronikCategory->id,
            ],
            [
                'name' => 'Power Bank Hippo Ilo P10 10000mAh',
                'brand' => 'Hippo',
                'description' => 'Power Bank Hippo Ilo P10 kapasitas 10000mAh dengan Fast Charging 18W, dual USB port, LED indicator, desain compact. Brand power bank Indonesia terpercaya.',
                'price' => 199000,
                'sale_price' => 169000,
                'stock' => 80,
                'weight' => 250,
                'status' => Product::STATUS_READY,
                'item_type' => Product::ITEM_TYPE_PRODUCT,
                'category_id' => $elektronikCategory->id,
            ],
        ];

        foreach ($products as $productData) {
            $slug = Str::slug($productData['name']);
            Product::updateOrCreate(
                ['slug' => $slug],
                array_merge([
                    'store_id' => $store->id,
                    'min_order' => 1,
                    'visibility_scope' => Product::VISIBILITY_GLOBAL,
                ], $productData)
            );
        }

        $this->command?->info('ProductSeeder: 5 produk elektronik Indonesia berhasil dibuat.');
    }
}
