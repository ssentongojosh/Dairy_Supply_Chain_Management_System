<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RawMaterial;

class RawMaterialPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Get all raw materials
        $rawMaterials = RawMaterial::all();

        // Update each raw material with a random price
        foreach ($rawMaterials as $material) {
            $randomPrice = rand(1000, 3000);
            $material->price = $randomPrice;
            $material->save();
        }

        $this->command->info('Updated ' . $rawMaterials->count() . ' raw materials with random prices.');
    }
}
