<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;

class DairyProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $csv = Reader::createFromPath(database_path('seeders/data/dairy_products.csv'), 'r');
    $csv->setHeaderOffset(0);

    foreach ($csv->getRecords() as $record) {
        DB::table('dairy_products')->insert([
            'Year' => $record['Year'],
            'Factory_Name' => $record['Factory_Name'],
            'Product' => $record['Product'],
            'Unity' => $record['Unity'],
            'Month' => $record['Month'],
            'Quantity' => $record['Quantity'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
}
