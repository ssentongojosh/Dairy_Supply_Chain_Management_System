<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\Role;

class FarmerSeeder extends Seeder
{
    public function run(): void
    {
        // Create test farmers with dairy farming background
        $farmers = [
            [
                'name' => 'Green Valley Dairy Farm',
                'email' => 'farmer@test.com',
                'password' => Hash::make('password'),
                'role' => Role::FARMER,
                'verified' => true,
            ],
            [
                'name' => 'Sunshine Organic Dairy',
                'email' => 'sunshine@farm.com',
                'password' => Hash::make('password'),
                'role' => Role::FARMER,
                'verified' => true,
            ],
            [
                'name' => 'Mountain View Farm',
                'email' => 'mountainview@dairy.com',
                'password' => Hash::make('password'),
                'role' => Role::FARMER,
                'verified' => true,
            ],
            [
                'name' => 'Fresh Meadows Dairy',
                'email' => 'freshmeadows@farm.com',
                'password' => Hash::make('password'),
                'role' => Role::FARMER,
                'verified' => true,
            ],
            [
                'name' => 'Heritage Family Farm',
                'email' => 'heritage@dairy.com',
                'password' => Hash::make('password'),
                'role' => Role::FARMER,
                'verified' => true,
            ],
        ];

        foreach ($farmers as $farmerData) {
            User::firstOrCreate(
                ['email' => $farmerData['email']],
                $farmerData
            );
        }

        $this->command->info('Farmer seeder completed successfully!');
        $this->command->info('Created ' . count($farmers) . ' farmer accounts.');
    }
}
