<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;

class CustomerSegmentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $csv = Reader::createFromPath(database_path('seeders/data/customer_segmentation.csv'), 'r');
    $csv->setHeaderOffset(0);

    foreach ($csv->getRecords() as $record) {
        DB::table('customer_segmentation')->insert([
            'Customer_ID' => $record['Customer ID'],
            'Age' => $record['Age'],
            'Gender' => $record['Gender'],
            'Annual_Income' => $record['Annual Income'],
            'Spending_Score' => $record['Spending Score'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
}
