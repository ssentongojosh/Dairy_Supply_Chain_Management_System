<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use App\Enums\Role;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test retailer
        $retailer = User::where('email', 'retailer@test.com')->first();

        if (!$retailer) {
            $this->command->error('Test retailer not found. Please run OrderSeeder first.');
            return;
        }

        // Get some products to add to inventory
        $products = Product::take(8)->get();

        if ($products->count() == 0) {
            $this->command->error('No products found. Please run OrderSeeder first to create products.');
            return;
        }

        // Create inventory items with various stock levels
        $inventoryData = [
            ['quantity' => 25, 'reorder_point' => 10, 'unit_cost' => 3.00, 'selling_price' => 3.50, 'location' => 'Shelf A1'],
            ['quantity' => 5, 'reorder_point' => 15, 'unit_cost' => 3.25, 'selling_price' => 3.75, 'location' => 'Shelf A2'],
            ['quantity' => 0, 'reorder_point' => 20, 'unit_cost' => 3.00, 'selling_price' => 3.50, 'location' => 'Shelf A3'],
            ['quantity' => 100, 'reorder_point' => 25, 'unit_cost' => 5.50, 'selling_price' => 5.99, 'location' => 'Cooler B1'],
            ['quantity' => 15, 'reorder_point' => 12, 'unit_cost' => 4.00, 'selling_price' => 4.50, 'location' => 'Cooler B2'],
            ['quantity' => 3, 'reorder_point' => 10, 'unit_cost' => 4.75, 'selling_price' => 5.25, 'location' => 'Cooler B3'],
            ['quantity' => 75, 'reorder_point' => 20, 'unit_cost' => 4.50, 'selling_price' => 4.99, 'location' => 'Cooler C1'],
            ['quantity' => 12, 'reorder_point' => 15, 'unit_cost' => 4.50, 'selling_price' => 4.99, 'location' => 'Cooler C2'],
        ];

        foreach ($products->take(8) as $index => $product) {
            if (isset($inventoryData[$index])) {
                Inventory::firstOrCreate(
                    [
                        'user_id' => $retailer->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => $inventoryData[$index]['quantity'],
                        'reorder_point' => $inventoryData[$index]['reorder_point'],
                        'unit_cost' => $inventoryData[$index]['unit_cost'],
                        'selling_price' => $inventoryData[$index]['selling_price'],
                        'location' => $inventoryData[$index]['location'],
                        'last_restocked_at' => now()->subDays(rand(1, 30)),
                    ]
                );
            }
        }

        $this->command->info('Inventory seeder completed successfully!');
        $this->command->info('Created inventory items for ' . $products->count() . ' products.');
    }
}
