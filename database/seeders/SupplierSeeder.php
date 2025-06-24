<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\Role;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'AgriSupply Co.',
                'email' => 'supplier@agrisupply.com',
                'password' => Hash::make('password123'),
                'role' => Role::SUPPLIER,
               
                'verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'FarmTech Solutions',
                'email' => 'contact@farmtech.ph',
                'password' => Hash::make('password123'),
                'role' => Role::SUPPLIER,
                
                'verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GreenHarvest Supplies',
                'email' => 'info@greenharvest.com',
                'password' => Hash::make('password123'),
                'role' => Role::SUPPLIER,
                
                'verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dairy Equipment Plus',
                'email' => 'sales@dairyequipment.ph',
                'password' => Hash::make('password123'),
                'role' => Role::SUPPLIER,
                
                'verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ProFeed Nutrition',
                'email' => 'orders@profeed.com',
                'password' => Hash::make('password123'),
                'role' => Role::SUPPLIER,
                
                'verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($suppliers as $supplierData) {
            User::create($supplierData);
        }

        $this->command->info('Supplier users created successfully!');
    }
}
