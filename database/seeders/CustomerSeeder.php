<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $customers = [
            [
                'name' => 'Customer Demo',
                'email' => 'customer@example.com',
            ],
            [
                'name' => 'Customer Retail',
                'email' => 'retail@example.com',
            ],
        ];

        foreach ($customers as $customer) {
            $user = User::firstOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                ]
            );

            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
    }
}
