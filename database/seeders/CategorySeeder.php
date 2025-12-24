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
        // 15 Kategori utama (tanpa sub-kategori untuk menyederhanakan)
        $categories = [
            ['name' => 'Elektronik', 'parent' => null],
            ['name' => 'Laptop & Aksesoris', 'parent' => 'elektronik'],
            ['name' => 'Smartphone & Tablet', 'parent' => 'elektronik'],
            ['name' => 'Peralatan Rumah Tangga', 'parent' => null],
            ['name' => 'Fashion Pria', 'parent' => null],
            ['name' => 'Fashion Wanita', 'parent' => null],
            ['name' => 'Olahraga & Outdoor', 'parent' => null],
            ['name' => 'Kesehatan & Kecantikan', 'parent' => null],
            ['name' => 'Ibu & Bayi', 'parent' => null],
            ['name' => 'Buku & Alat Tulis', 'parent' => null],
            ['name' => 'Hobi & Koleksi', 'parent' => null],
            ['name' => 'Otomotif', 'parent' => null],
            ['name' => 'Perkakas & Pertukangan', 'parent' => null],
            ['name' => 'Makanan & Minuman', 'parent' => null],
            ['name' => 'Komputer & Aksesoris', 'parent' => null],
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
