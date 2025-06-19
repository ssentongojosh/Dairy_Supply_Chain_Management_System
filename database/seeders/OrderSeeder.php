<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\Role;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create test users
        $retailer = User::firstOrCreate(
            ['email' => 'retailer@test.com'],
            [
                'name' => 'Test Retailer',
                'password' => bcrypt('password'),
                'role' => Role::RETAILER,
                'verified' => true,
            ]
        );

        $wholesaler1 = User::firstOrCreate(
            ['email' => 'wholesaler1@test.com'],
            [
                'name' => 'Dairy Fresh Wholesale',
                'password' => bcrypt('password'),
                'role' => Role::WHOLESALER,
                'verified' => true,
            ]
        );

        $wholesaler2 = User::firstOrCreate(
            ['email' => 'wholesaler2@test.com'],
            [
                'name' => 'Premium Dairy Supply',
                'password' => bcrypt('password'),
                'role' => Role::WHOLESALER,
                'verified' => true,
            ]
        );

        $farmer = User::firstOrCreate(
            ['email' => 'farmer@test.com'],
            [
                'name' => 'Green Valley Dairy Farm',
                'password' => bcrypt('password'),
                'role' => Role::FARMER,
                'verified' => true,
            ]
        );

        // Create test products - Dairy products
        $products = [
            [
                'name' => 'Strawberry Flavoured Milk',
                'price' => 3.50,
                'supplier_id' => $farmer->id,
            ],
            [
                'name' => 'Chocolate Flavoured Milk',
                'price' => 3.75,
                'supplier_id' => $farmer->id,
            ],
            [
                'name' => 'Vanilla Flavoured Milk',
                'price' => 3.50,
                'supplier_id' => $wholesaler1->id,
            ],
            [
                'name' => 'Greek Yoghurt',
                'price' => 5.99,
                'supplier_id' => $wholesaler1->id,
            ],
            [
                'name' => 'Natural Yoghurt',
                'price' => 4.50,
                'supplier_id' => $farmer->id,
            ],
            [
                'name' => 'Berry Yoghurt',
                'price' => 5.25,
                'supplier_id' => $wholesaler2->id,
            ],
            [
                'name' => 'Salted Butter',
                'price' => 4.99,
                'supplier_id' => $wholesaler1->id,
            ],
            [
                'name' => 'Unsalted Butter',
                'price' => 4.99,
                'supplier_id' => $wholesaler2->id,
            ],
            [
                'name' => 'Vanilla Ice Cream',
                'price' => 8.99,
                'supplier_id' => $wholesaler1->id,
            ],
            [
                'name' => 'Chocolate Ice Cream',
                'price' => 9.50,
                'supplier_id' => $wholesaler2->id,
            ],
            [
                'name' => 'Strawberry Ice Cream',
                'price' => 9.25,
                'supplier_id' => $farmer->id,
            ],
            [
                'name' => 'Cookies & Cream Ice Cream',
                'price' => 10.99,
                'supplier_id' => $wholesaler1->id,
            ],
        ];

        $createdProducts = [];
        foreach ($products as $productData) {
            $createdProducts[] = Product::firstOrCreate(
                ['name' => $productData['name']],
                $productData
            );
        }

        // Create sample orders with different statuses and dates
        $orders = [
            [
                'buyer_id' => $retailer->id,
                'seller_id' => $wholesaler1->id,
                'status' => 'delivered',
                'created_at' => Carbon::now()->subDays(30),
                'items' => [
                    ['product_id' => $createdProducts[3]->id, 'quantity' => 10, 'price' => 5.99], // Greek Yoghurt
                    ['product_id' => $createdProducts[6]->id, 'quantity' => 5, 'price' => 4.99], // Salted Butter
                ]
            ],
            [
                'buyer_id' => $retailer->id,
                'seller_id' => $farmer->id,
                'status' => 'shipped',
                'created_at' => Carbon::now()->subDays(5),
                'items' => [
                    ['product_id' => $createdProducts[0]->id, 'quantity' => 20, 'price' => 3.50], // Strawberry Flavoured Milk
                    ['product_id' => $createdProducts[1]->id, 'quantity' => 15, 'price' => 3.75], // Chocolate Flavoured Milk
                ]
            ],
            [
                'buyer_id' => $retailer->id,
                'seller_id' => $wholesaler2->id,
                'status' => 'processing',
                'created_at' => Carbon::now()->subDays(3),
                'items' => [
                    ['product_id' => $createdProducts[9]->id, 'quantity' => 8, 'price' => 9.50], // Chocolate Ice Cream
                ]
            ],
            [
                'buyer_id' => $retailer->id,
                'seller_id' => $wholesaler1->id,
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(1),
                'items' => [
                    ['product_id' => $createdProducts[8]->id, 'quantity' => 6, 'price' => 8.99], // Vanilla Ice Cream
                    ['product_id' => $createdProducts[11]->id, 'quantity' => 4, 'price' => 10.99], // Cookies & Cream Ice Cream
                ]
            ],
            [
                'buyer_id' => $retailer->id,
                'seller_id' => $farmer->id,
                'status' => 'cancelled',
                'created_at' => Carbon::now()->subDays(15),
                'items' => [
                    ['product_id' => $createdProducts[4]->id, 'quantity' => 25, 'price' => 4.50], // Natural Yoghurt
                ]
            ],
            [
                'buyer_id' => $retailer->id,
                'seller_id' => $wholesaler2->id,
                'status' => 'delivered',
                'created_at' => Carbon::now()->subDays(20),
                'items' => [
                    ['product_id' => $createdProducts[5]->id, 'quantity' => 12, 'price' => 5.25], // Berry Yoghurt
                    ['product_id' => $createdProducts[7]->id, 'quantity' => 8, 'price' => 4.99], // Unsalted Butter
                ]
            ],
            [
                'buyer_id' => $retailer->id,
                'seller_id' => $wholesaler1->id,
                'status' => 'shipped',
                'created_at' => Carbon::now()->subDays(2),
                'items' => [
                    ['product_id' => $createdProducts[2]->id, 'quantity' => 30, 'price' => 3.50], // Vanilla Flavoured Milk
                ]
            ],
            [
                'buyer_id' => $retailer->id,
                'seller_id' => $farmer->id,
                'status' => 'processing',
                'created_at' => Carbon::now()->subDays(7),
                'items' => [
                    ['product_id' => $createdProducts[10]->id, 'quantity' => 6, 'price' => 9.25], // Strawberry Ice Cream
                    ['product_id' => $createdProducts[0]->id, 'quantity' => 18, 'price' => 3.50], // Strawberry Flavoured Milk
                ]
            ],
        ];

        foreach ($orders as $orderData) {
            $items = $orderData['items'];
            unset($orderData['items']);

            // Calculate total amount
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }
            $orderData['total_amount'] = $totalAmount;

            $order = Order::create($orderData);

            // Create order items
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                ]);
            }
        }

        $this->command->info('Order seeder completed successfully!');
        $this->command->info('Created ' . count($orders) . ' sample orders for the test retailer.');
        $this->command->info('Created ' . count($products) . ' dairy products.');
    }
}
