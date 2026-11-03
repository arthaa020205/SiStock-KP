<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'Petugas Gudang',
            'guard_name' => 'web',
        ]);

        // Membuat akun Admin jika kredensial tersedia
        if (env('SEED_ADMIN_EMAIL') && env('SEED_ADMIN_PASSWORD')) {
            $admin = User::updateOrCreate(
                ['email' => env('SEED_ADMIN_EMAIL')],
                [
                    'name' => 'Administrator',
                    'password' => env('SEED_ADMIN_PASSWORD'),
                ]
            );

            $admin->assignRole('Admin');
        }

        // Membuat akun Petugas jika kredensial tersedia
        if (env('SEED_PETUGAS_EMAIL') && env('SEED_PETUGAS_PASSWORD')) {
            $petugas = User::updateOrCreate(
                ['email' => env('SEED_PETUGAS_EMAIL')],
                [
                    'name' => 'Petugas Gudang',
                    'password' => env('SEED_PETUGAS_PASSWORD'),
                ]
            );

            $petugas->assignRole('Petugas Gudang');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}