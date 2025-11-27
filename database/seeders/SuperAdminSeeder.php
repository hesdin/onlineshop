<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.super_admin_email', env('SUPERADMIN_EMAIL', 'superadmin@example.com'));
        $name = config('app.super_admin_name', env('SUPERADMIN_NAME', 'Super Admin'));
        $password = config('app.super_admin_password', env('SUPERADMIN_PASSWORD', 'password'));

        $role = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'web',
        ]);

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'remember_token' => Str::random(10),
            ],
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
