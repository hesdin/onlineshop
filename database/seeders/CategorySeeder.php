<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Elektronik', 'parent' => null],
            ['name' => 'Laptop & Aksesoris', 'parent' => 'elektronik'],
            ['name' => 'Smartphone & Tablet', 'parent' => 'elektronik'],
            ['name' => 'Peralatan Rumah Tangga', 'parent' => null],
            ['name' => 'Dapur & Masak', 'parent' => 'peralatan-rumah-tangga'],
            ['name' => 'Kebersihan Rumah', 'parent' => 'peralatan-rumah-tangga'],
            ['name' => 'Fashion Pria', 'parent' => null],
            ['name' => 'Fashion Wanita', 'parent' => null],
            ['name' => 'Aksesoris Fashion', 'parent' => 'fashion-wanita'],
            ['name' => 'Olahraga & Outdoor', 'parent' => null],
            ['name' => 'Kesehatan & Kecantikan', 'parent' => null],
            ['name' => 'Perawatan Kulit', 'parent' => 'kesehatan-kecantikan'],
            ['name' => 'Perawatan Rambut', 'parent' => 'kesehatan-kecantikan'],
            ['name' => 'Ibu & Bayi', 'parent' => null],
            ['name' => 'Mainan Anak', 'parent' => 'ibu-bayi'],
            ['name' => 'Buku & Alat Tulis', 'parent' => null],
            ['name' => 'Hobi & Koleksi', 'parent' => null],
            ['name' => 'Otomotif', 'parent' => null],
            ['name' => 'Perkakas & Pertukangan', 'parent' => null],
            ['name' => 'Makanan & Minuman', 'parent' => null],
        ];

        foreach ($categories as $category) {
            $slug = Str::slug($category['name']);
            $parentSlug = $category['parent'];
            $parentId = null;

            if ($parentSlug) {
                $parent = Category::firstOrCreate(
                    ['slug' => $parentSlug],
                    ['name' => Str::headline(str_replace('-', ' ', $parentSlug))]
                );
                $parentId = $parent->id;
            }

            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'parent_id' => $parentId,
                ]
            );
        }
    }
}
