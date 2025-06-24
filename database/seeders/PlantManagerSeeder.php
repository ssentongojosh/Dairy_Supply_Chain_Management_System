<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;

class PlantManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plantManagers = [
            [
                'name' => 'James Okello',
                'email' => 'james.okello@milkplant.com',
                'password' => Hash::make('password'),
                'role' => Role::PLANT_MANAGER,
                'verified' => true,
                
             
            ],
            [
                'name' => 'Sarah Namukasa',
                'email' => 'sarah.namukasa@freshprocessing.com',
                'password' => Hash::make('password'),
                'role' => Role::PLANT_MANAGER,
                'verified' => true,
                
                
            ],
            [
                'name' => 'David Musoke',
                'email' => 'david.musoke@qualitydairy.com',
                'password' => Hash::make('password'),
                'role' => Role::PLANT_MANAGER,
                'verified' => true,
               
               
            ],
            [
                'name' => 'Grace Nakato',
                'email' => 'grace.nakato@modernmilk.com',
                'password' => Hash::make('password'),
                'role' => Role::PLANT_MANAGER,
                'verified' => true,
                
               
            ]
        ];

        foreach ($plantManagers as $plantManagerData) {
            User::create($plantManagerData);
        }

        $this->command->info('Plant Manager users created successfully!');
    }
}
