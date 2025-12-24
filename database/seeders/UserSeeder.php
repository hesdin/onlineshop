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

        // Create 1 superadmin user
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superadmin->syncRoles([$superadminRole]);

        // Create 1 seller user
        $seller = User::firstOrCreate(
            ['email' => 'seller@mail.com'],
            [
                'name' => 'Seller Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $seller->syncRoles([$sellerRole]);

        // Create 1 customer user
        $customer = User::firstOrCreate(
            ['email' => 'customer@mail.com'],
            [
                'name' => 'Customer Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $customer->syncRoles([$customerRole]);
    }
}
