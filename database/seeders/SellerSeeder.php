<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        // Get the seller user and store
        $seller = User::role('seller')->first();
        $store = Store::where('slug', 'toko-elektronik-jaya')->first();

        if (!$seller) {
            $this->command?->warn('SellerSeeder: tidak ada user dengan role seller. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        if (!$store) {
            $this->command?->warn('SellerSeeder: toko tidak ditemukan. Jalankan StoreSeeder terlebih dahulu.');
            return;
        }

        // Make sure store is linked to seller
        if ($store->user_id !== $seller->id) {
            $store->update(['user_id' => $seller->id]);
        }

        $this->command?->info('SellerSeeder: seller dan toko sudah terhubung.');
    }
}
