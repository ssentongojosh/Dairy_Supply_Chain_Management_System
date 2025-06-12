<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@dscms.com',
            'password' => Hash::make('admin123'), // Change this to a secure password
            'role' => 'admin',
        ]);

        // Add additional admin users as needed
        User::create([
            'name' => 'Team Admin',
            'email' => 'team@dscms.com',
            'password' => Hash::make('team123'), // Change this to a secure password
            'role' => 'admin',
        ]);
    }
}
