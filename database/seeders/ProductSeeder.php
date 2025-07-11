<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dairyProducts = [
            [
                'name' => 'Pasteurized Milk 3.0%',
                'sku' => 'PM-30',
                'description' => 'High quality pasteurized milk with 3.0% fat content',
                'category' => 'Milk',
                'price' => 2.50,
                'is_active' => true
            ],
            [
                'name' => 'Pasteurized Milk 2.5%',
                'sku' => 'PM-25',
                'description' => 'High quality pasteurized milk with 2.5% fat content',
                'category' => 'Milk',
                'price' => 2.40,
                'is_active' => true
            ],
            [
                'name' => 'Pasteurized Milk 1.5%',
                'sku' => 'PM-15',
                'description' => 'Low fat pasteurized milk with 1.5% fat content',
                'category' => 'Milk',
                'price' => 2.30,
                'is_active' => true
            ],
            [
                'name' => 'Greek Yogurt',
                'sku' => 'YGT-GRK',
                'description' => 'Creamy Greek-style yogurt',
                'category' => 'Yogurt',
                'price' => 4.50,
                'is_active' => true
            ],
            [
                'name' => 'Natural Yogurt',
                'sku' => 'YGT-NAT',
                'description' => 'Plain natural yogurt',
                'category' => 'Yogurt',
                'price' => 3.80,
                'is_active' => true
            ],
            [
                'name' => 'Cheddar Cheese',
                'sku' => 'CHE-CHD',
                'description' => 'Aged cheddar cheese',
                'category' => 'Cheese',
                'price' => 12.00,
                'is_active' => true
            ],
            [
                'name' => 'Mozzarella Cheese',
                'sku' => 'CHE-MOZ',
                'description' => 'Fresh mozzarella cheese',
                'category' => 'Cheese',
                'price' => 10.50,
                'is_active' => true
            ],
            [
                'name' => 'Salted Butter',
                'sku' => 'BUT-SAL',
                'description' => 'Premium salted butter',
                'category' => 'Butter',
                'price' => 8.50,
                'is_active' => true
            ],
            [
                'name' => 'Unsalted Butter',
                'sku' => 'BUT-UNS',
                'description' => 'Premium unsalted butter',
                'category' => 'Butter',
                'price' => 8.50,
                'is_active' => true
            ],
            [
                'name' => 'Heavy Cream',
                'sku' => 'CRM-HVY',
                'description' => 'Rich heavy cream for cooking',
                'category' => 'Cream',
                'price' => 5.50,
                'is_active' => true
            ]
        ];

        foreach ($dairyProducts as $product) {
            Product::create($product);
        }

        $this->command->info('Created ' . count($dairyProducts) . ' dairy products');
    }
}
