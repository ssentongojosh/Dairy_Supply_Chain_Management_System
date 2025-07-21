<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;

class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@dairyplant.com',
                'password' => Hash::make('worker123'),
                'role' => Role::WORKER->value,
                'verified' => true,
                'business_document_path' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mary Johnson',
                'email' => 'mary.johnson@dairyplant.com',
                'password' => Hash::make('worker123'),
                'role' => Role::WORKER->value,
                'verified' => true,
                'business_document_path' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peter Okello',
                'email' => 'peter.okello@dairyplant.com',
                'password' => Hash::make('worker123'),
                'role' => Role::WORKER->value,
                'verified' => true,
                'business_document_path' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sarah Nakato',
                'email' => 'sarah.nakato@dairyplant.com',
                'password' => Hash::make('worker123'),
                'role' => Role::WORKER->value,
                'verified' => true,
                'business_document_path' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'David Mukasa',
                'email' => 'david.mukasa@dairyplant.com',
                'password' => Hash::make('worker123'),
                'role' => Role::WORKER->value,
                'verified' => true,
                'business_document_path' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grace Achieng',
                'email' => 'grace.achieng@dairyplant.com',
                'password' => Hash::make('worker123'),
                'role' => Role::WORKER->value,
                'verified' => true,
                'business_document_path' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Moses Ssali',
                'email' => 'moses.ssali@dairyplant.com',
                'password' => Hash::make('worker123'),
                'role' => Role::WORKER->value,
                'verified' => true,
                'business_document_path' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Betty Namuli',
                'email' => 'betty.namuli@dairyplant.com',
                'password' => Hash::make('worker123'),
                'role' => Role::WORKER->value,
                'verified' => true,
                'business_document_path' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($workers as $worker) {
            // Check if user already exists to avoid duplicates
            $existingUser = User::where('email', $worker['email'])->first();

            if (!$existingUser) {
                User::create($worker);
                $this->command->info("Created verified worker: {$worker['name']} ({$worker['email']})");
            } else {
                $this->command->warn("Worker already exists: {$worker['email']}");
            }
        }

        $this->command->info('Worker seeder completed successfully!');
    }
}
