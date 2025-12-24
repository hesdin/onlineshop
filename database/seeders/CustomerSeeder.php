<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Customer sudah dibuat di UserSeeder
        // Seeder ini dipertahankan untuk backward compatibility
        $this->command?->info('CustomerSeeder: customer sudah dibuat di UserSeeder.');
    }
}
