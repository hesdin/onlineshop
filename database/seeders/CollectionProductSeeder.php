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
            $pick = $products->random(min($products->count(), 8))->pluck('id')->all();
            $collection->products()->syncWithoutDetaching($pick);
        });
    }
}
