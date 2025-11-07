<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            RolePermissionSeeder::class,
            DefaultSettingsSeeder::class,
            DemoSeeder::class,
            \Database\Seeders\ProductSeeder::class,
        ]);

        if (config('verifactu.sandbox.enabled')) {
            $this->call(VerifactuSandboxSeeder::class);
        }
    }
}