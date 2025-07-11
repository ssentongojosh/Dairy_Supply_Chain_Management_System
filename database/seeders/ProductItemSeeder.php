<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductItem;
use Carbon\Carbon;

class ProductItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users by role
        $plantManagers = User::where('role', 'plant_manager')->get();
        $wholesalers = User::where('role', 'wholesaler')->get();
        $retailers = User::where('role', 'retailer')->get();

        // Get all products
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Run ProductSeeder first.');
            return;
        }

        // Seed for Plant Managers (they produce products)
        foreach ($plantManagers as $manager) {
            foreach ($products->take(8) as $product) { // Plant managers have most products
                ProductItem::create([
                    'product_id' => $product->id,
                    'user_id' => $manager->id,
                    'quantity' => rand(100, 500),
                    'cost_price' => $product->price * 0.8, // Cost is 80% of selling price
                    'selling_price' => $product->price,
                    'minimum_stock' => rand(20, 50),
                    'maximum_stock' => rand(600, 1000),
                    'manufacture_date' => Carbon::now()->subDays(rand(1, 5)),
                    'expiry_date' => Carbon::now()->addDays(rand(7, 30)),
                    'batch_number' => 'BATCH-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'status' => 'active'
                ]);
            }
        }

        // Seed for Wholesalers (they buy and resell)
        foreach ($wholesalers as $wholesaler) {
            foreach ($products->random(6) as $product) { // Wholesalers have fewer products
                ProductItem::create([
                    'product_id' => $product->id,
                    'user_id' => $wholesaler->id,
                    'quantity' => rand(50, 200),
                    'cost_price' => $product->price, // They buy at selling price
                    'selling_price' => $product->price * 1.15, // 15% markup
                    'minimum_stock' => rand(10, 30),
                    'maximum_stock' => rand(300, 500),
                    'expiry_date' => Carbon::now()->addDays(rand(5, 25)),
                    'batch_number' => 'WH-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'status' => 'active'
                ]);
            }
        }

        // Seed for Retailers (they sell to consumers)
        foreach ($retailers as $retailer) {
            foreach ($products->random(4) as $product) { // Retailers have fewer products
                ProductItem::create([
                    'product_id' => $product->id,
                    'user_id' => $retailer->id,
                    'quantity' => rand(20, 100),
                    'cost_price' => $product->price * 1.15, // They buy from wholesalers
                    'selling_price' => $product->price * 1.30, // 30% markup from base
                    'minimum_stock' => rand(5, 15),
                    'maximum_stock' => rand(150, 250),
                    'expiry_date' => Carbon::now()->addDays(rand(3, 20)),
                    'batch_number' => 'RT-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'status' => 'active'
                ]);
            }
        }

        // Add some low stock items for testing
        $lowStockItems = ProductItem::inRandomOrder()->take(3)->get();
        foreach ($lowStockItems as $item) {
            $item->update([
                'quantity' => rand(1, $item->minimum_stock - 1)
            ]);
        }

        // Add some expired items for testing
        $expiredItems = ProductItem::inRandomOrder()->take(2)->get();
        foreach ($expiredItems as $item) {
            $item->update([
                'expiry_date' => Carbon::now()->subDays(rand(1, 5)),
                'status' => 'expired'
            ]);
        }

        $totalItems = ProductItem::count();
        $this->command->info("Created {$totalItems} product items for users");
    }
}
