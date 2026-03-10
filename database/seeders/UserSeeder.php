<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Create Staff User
        $staff = User::firstOrCreate(
            ['email' => 'staff@gmail.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('Staff@123'),
                'email_verified_at' => now(),
            ]
        );
        $staff->assignRole('staff');

        // Create Regular User
        $user = User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('User@123'),
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('user');
    }
}
