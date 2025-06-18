<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class CustomerSegmentationSeeder extends Seeder
{
    public function run()
    {
        $csvFile = database_path('seeders/Dataset/Mall_Customers.csv');
        $csv = Reader::createFromPath($csvFile, 'r');
        $csv->setHeaderOffset(0); // First row as header

        $records = $csv->getRecords();
        foreach ($records as $record) {
            DB::table('customer_segmentation')->insert([
                'customer_id' => $record['customer_id'],
                'gender' => $record['gender'],
                'age' => $record['age'],
                'annual_income' => $record['annual_income'],
                'spending_score' => $record['spending_score'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
