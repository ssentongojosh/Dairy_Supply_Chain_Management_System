<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RawMaterial;


class RawMaterialsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //raw materials
        $materials = [
            [
                'name' => 'Raw Milk',
                'unit' => 'liters',
                'quantity' => 1200,
                'reorder_threshold' => 500,
                'expiry' => now()->addMonths(2),
            ],
            [
                'name' => 'Condensed Milk',
                'unit' => 'liters',
                'quantity' => 600,
                'reorder_threshold' => 200,
                'expiry' => now()->addMonths(2),
            ],
            [
                'name' => 'Sugar',
                'unit' => 'kilograms',
                'quantity' => 400,
                'reorder_threshold' => 150,
                'expiry' => now()->addMonths(12),
            ],
            [
                'name' => 'Salt',
                'unit' => 'kilograms',
                'quantity' => 150,
                'reorder_threshold' => 50,
                'expiry' => now()->addMonths(15),
            ],
            [
                'name' => 'Stabilizers',
                'unit' => 'kilograms',
                'quantity' => 80,
                'reorder_threshold' => 30,
                'expiry' => now()->addMonths(3),
            ],
            [
                'name' => 'Emulsifiers',
                'unit' => 'kilograms',
                'quantity' => 70,
                'reorder_threshold' => 25,
                'expiry' => now()->addMonths(3),
            ],
            [
                'name' => 'Cultures (Lactic Acid Bacteria)',
                'unit' => 'grams',
                'quantity' => 500,
                'reorder_threshold' => 200,
                'expiry' => now()->addMonths(10),
            ],
            [
                'name' => 'Flavours',
                'unit' => 'milliliters',
                'quantity' => 1500,
                'reorder_threshold' => 600,
                'expiry' => now()->addMonths(12),
            ],
            [
                'name' => 'Food Colour',
                'unit' => 'milliliters',
                'quantity' => 800,
                'reorder_threshold' => 300,
                'expiry' => now()->addMonths(16),
            ],
            [
                'name' => 'Preservatives',
                'unit' => 'kilograms',
                'quantity' => 90,
                'reorder_threshold' => 40,
                'expiry' => now()->addMonths(24),
            ],
            [
                'name' => 'Citric Acid',
                'unit' => 'kilograms',
                'quantity' => 60,
                'reorder_threshold' => 20,
                'expiry' => now()->addMonths(8),
            ],
            [
                'name' => 'Distilled Water',
                'unit' => 'liters',
                'quantity' => 2000,
                'reorder_threshold' => 800,
                'expiry' => now()->addMonths(24),
            ],
            [
                'name' => 'Packaging Material',
                'unit' => 'units',
                'quantity' => 5000,
                'reorder_threshold' => 2000,
                'expiry' => now()->addMonths(36),
            ],
            [
                'name' => 'Vitamins',
                'unit' => 'grams',
                'quantity' => 300,
                'reorder_threshold' => 100,
                'expiry' => now()->addMonths(4),
            ],
        ];

        foreach ($materials as $material) {
            RawMaterial::create($material);
        }
    }
}
