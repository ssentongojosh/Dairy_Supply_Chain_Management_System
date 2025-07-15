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
                'image' => 'Raw Milk.jpeg',
            ],
            [
                'name' => 'Condensed Milk',
                'unit' => 'liters',
                'quantity' => 600,
                'reorder_threshold' => 200,
                'expiry' => now()->addMonths(2),
                'image' => 'Condensed Milk.jpeg',
            ],
            [
                'name' => 'Sugar',
                'unit' => 'kilograms',
                'quantity' => 400,
                'reorder_threshold' => 150,
                'expiry' => now()->addMonths(12),
                'image' => 'Sugar.jpeg',
            ],
            [
                'name' => 'Salt',
                'unit' => 'kilograms',
                'quantity' => 150,
                'reorder_threshold' => 50,
                'expiry' => now()->addMonths(15),
                'image' => 'Salt.jpeg',
            ],
            [
                'name' => 'Stabilizers',
                'unit' => 'kilograms',
                'quantity' => 80,
                'reorder_threshold' => 30,
                'expiry' => now()->addMonths(3),
                'image' => 'Stabilizer.jpeg',
            ],
            [
                'name' => 'Emulsifiers',
                'unit' => 'kilograms',
                'quantity' => 70,
                'reorder_threshold' => 25,
                'expiry' => now()->addMonths(3),
                'image' => 'Emulsifier.jpeg',
            ],
            [
                'name' => 'Cultures (Lactic Acid Bacteria)',
                'unit' => 'grams',
                'quantity' => 500,
                'reorder_threshold' => 200,
                'expiry' => now()->addMonths(10),
                'image' => 'Cultures.jpeg',
            ],
            [
                'name' => 'Flavours',
                'unit' => 'milliliters',
                'quantity' => 1500,
                'reorder_threshold' => 600,
                'expiry' => now()->addMonths(12),
                'image' => 'Flavours.jpeg',
            ],
            [
                'name' => 'Food Colour',
                'unit' => 'milliliters',
                'quantity' => 800,
                'reorder_threshold' => 300,
                'expiry' => now()->addMonths(16),
                'image' => 'Food Colour.jpeg',
            ],
            [
                'name' => 'Preservatives',
                'unit' => 'kilograms',
                'quantity' => 90,
                'reorder_threshold' => 40,
                'expiry' => now()->addMonths(24),
                'image' => 'Preservatives.jpeg',
            ],
            [
                'name' => 'Citric Acid',
                'unit' => 'kilograms',
                'quantity' => 60,
                'reorder_threshold' => 20,
                'expiry' => now()->addMonths(8),
                'image' => 'Citric Acid.jpeg',
            ],
            [
                'name' => 'Distilled Water',
                'unit' => 'liters',
                'quantity' => 2000,
                'reorder_threshold' => 800,
                'expiry' => now()->addMonths(24),
                'image' => 'Distilled Water.jpeg',
            ],
            [
                'name' => 'Packaging Material',
                'unit' => 'units',
                'quantity' => 5000,
                'reorder_threshold' => 2000,
                'expiry' => now()->addMonths(36),
                'image' => 'Packaging Materials.jpeg',
            ],
            [
                'name' => 'Vitamins',
                'unit' => 'grams',
                'quantity' => 300,
                'reorder_threshold' => 100,
                'expiry' => now()->addMonths(4),
                'image' => 'Vitamins.jpeg',
            ],
        ];

        foreach ($materials as $material) {
            RawMaterial::updateOrCreate(
                ['name' => $material['name']], // Find by name
                $material // Update or create with all fields
            );
        }
    }
}
