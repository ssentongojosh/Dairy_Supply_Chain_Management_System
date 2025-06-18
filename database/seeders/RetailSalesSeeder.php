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
    $csvFile = database_path('seeders/Datasets/retail sales.csv');
    $csv = Reader::createFromPath($csvFile, 'r');
    $csv->setHeaderOffset(0);

    foreach ($csv->getRecords() as $record) {
        DB::table('retail_sales')->insert([
            'ccdata' => $record['ccdata'],
            'venda' => $record['venda'],
            'estoque' => $record['estoque'],
            'preco' => $record['preco'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
}
