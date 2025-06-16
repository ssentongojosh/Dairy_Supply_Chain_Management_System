<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = ['admin', 'retailer', 'wholesaler', 'factory', 'supplier'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create users and assign roles
        $users = [
            ['name' => 'Retailer One', 'email' => 'retailer@example.com', 'role' => 'retailer'],
            ['name' => 'Wholesaler One', 'email' => 'wholesaler@example.com', 'role' => 'wholesaler'],
            ['name' => 'Factory One', 'email' => 'factory@example.com', 'role' => 'factory'],
            ['name' => 'Supplier One', 'email' => 'supplier@example.com', 'role' => 'supplier'],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password') // 👈 Change this in production
                ]
            );

            $user->assignRole($userData['role']);
        }
    }
}

