<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionProductSeeder extends Seeder
{
    public function run(): void
    {
        $collections = Collection::all();
        $products = Product::all();

        if ($collections->isEmpty() || $products->isEmpty()) {
            $this->command?->warn('CollectionProductSeeder skipped because collections or products are empty.');
            return;
        }

        $collections->each(function (Collection $collection) use ($products) {
            // Assign all 5 products to each collection
            $pick = $products->pluck('id')->all();
            $collection->products()->syncWithoutDetaching($pick);
        });

        $this->command?->info('CollectionProductSeeder: produk berhasil ditambahkan ke semua collection.');
    }
}
