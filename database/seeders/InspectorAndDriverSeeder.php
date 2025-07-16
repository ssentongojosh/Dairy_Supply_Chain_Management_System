<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;

class InspectorAndDriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Inspectors
        $inspectors = [
            [
                'name' => 'John Inspector',
                'email' => 'inspector1@dscms.com',
            ],
            [
                'name' => 'Mary Field',
                'email' => 'inspector2@dscms.com',
            ],
            [
                'name' => 'Robert Quality',
                'email' => 'inspector3@dscms.com',
            ],
            [
                'name' => 'Sarah Compliance',
                'email' => 'inspector4@dscms.com',
            ],
            [
                'name' => 'David Audit',
                'email' => 'inspector5@dscms.com',
            ],
        ];

        // Drivers
        $drivers = [
            [
                'name' => 'Michael Driver',
                'email' => 'driver1@dscms.com',
            ],
            [
                'name' => 'Lisa Delivery',
                'email' => 'driver2@dscms.com',
            ],
            [
                'name' => 'James Transport',
                'email' => 'driver3@dscms.com',
            ],
            [
                'name' => 'Patricia Logistics',
                'email' => 'driver4@dscms.com',
            ],
            [
                'name' => 'Thomas Shipment',
                'email' => 'driver5@dscms.com',
            ],
        ];

        // Create inspector users
        $this->createUsers($inspectors, Role::INSPECTOR);

        // Create driver users
        $this->createUsers($drivers, Role::DRIVER);

        $this->command->info('Inspector and Driver users created successfully!');
    }

    /**
     * Create users with the specified role
     *
     * @param array $users
     * @param Role $role
     * @return void
     */
    private function createUsers(array $users, Role $role)
    {
        foreach ($users as $userData) {
            // Check if user already exists
            if (!User::where('email', $userData['email'])->exists()) {
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'verified' => true,
                    'business_document_path' => null,
                    'email_verified_at' => now(),
                ]);
            } else {
                $this->command->warn("User {$userData['email']} already exists. Skipping.");
            }
        }
    }
}
