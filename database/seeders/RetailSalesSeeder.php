<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;

class RetailSalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $csv = Reader::createFromPath(database_path('seeders/data/retail_sales.csv'), 'r');
    $csv->setHeaderOffset(0);

    foreach ($csv->getRecords() as $record) {
        DB::table('retail_sales')->insert([
            'invoice_no' => $record['invoice_no'],
            'customer_id' => $record['customer_id'],
            'gender' => $record['gender'],
            'age' => $record['age'],
            'category' => $record['category'],
            'quantity' => $record['quantity'],
            'price' => $record['price'],
            'payment_method' => $record['payment_method'],
            'invoice_date' => $record['invoice_date'],
            'shopping_mall' => $record['shopping_mall'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
}
