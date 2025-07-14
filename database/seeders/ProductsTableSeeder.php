<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //sample data to work with
        $products = [
            [
                'name' => 'Yogurt',
                'price' => 4000,
                'quantity' => 200,
                'supplier_id' => 2,
                'image' => 'yogurt.jpeg',
            ],
            [
                'name' => 'Cheese',
                'price' => 8500,
                'quantity' => 120,
                'supplier_id' => 3,
                'image' => 'cheese.jpeg',
            ],
            [
                'name' => 'Butter',
                'price' => 6000,
                'quantity' => 150,
                'supplier_id' => 1,
                'image' => 'butter.jpeg',
            ],
            [
                'name' => 'Powdered-milk',
                'price' => 9500,
                'quantity' => 300,
                'supplier_id' => 2,
                'image' => 'powdered-milk.jpeg',
            ],
            [
                'name' => 'Ghee',
                'price' => 7000,
                'quantity' => 80,
                'supplier_id' => 3,
                'image' => 'ghee.jpeg',
            ],
            [
                'name' => 'Milkshake',
                'price' => 3000,
                'quantity' => 220,
                'supplier_id' => 1,
                'image' => 'milkshake.jpeg',
            ],
            [
                'name' => 'Ice-cream',
                'price' => 5000,
                'quantity' => 100,
                'supplier_id' => 4,
                'image' => 'ice-cream.jpeg',
            ],
            [
                'name' => 'UHT-milk',
                'price' => 4500,
                'quantity' => 400,
                'supplier_id' => 2,
                'image' => 'uht-milk.jpeg',
            ],
            [
                'name' => 'Flavoured-milk',
                'price' => 3500,
                'quantity' => 180,
                'supplier_id' => 4,
                'image' => 'flavoured-milk.jpeg',
            ],
            [
                'name' => 'Cream',
                'price' => 6000,
                'quantity' => 130,
                'supplier_id' => 1,
                'image' => 'cream.jpeg',
            ],
            [
                'name' => 'Custard',
                'price' => 5500,
                'quantity' => 90,
                'supplier_id' => 3,
                'image' => 'custard.jpeg',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
