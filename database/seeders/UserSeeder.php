<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // Create superadmin user
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superadmin->syncRoles([$superadminRole]);

        // Create seller users
        for ($i = 1; $i <= 5; $i++) {
            $seller = User::firstOrCreate(
                ['email' => "seller{$i}@example.com"],
                [
                    'name' => "Seller {$i}",
                    'password' => Hash::make('password'),
                ]
            );
            $seller->syncRoles([$sellerRole]);
        }

        // Create customer users
        for ($i = 1; $i <= 10; $i++) {
            $customer = User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => "Customer {$i}",
                    'password' => Hash::make('password'),
                ]
            );
            $customer->syncRoles([$customerRole]);
        }
    }
}
