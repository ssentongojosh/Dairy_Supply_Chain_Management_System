<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;

class MilkProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $csvFile = database_path('seeders/Dataset/Milk Production in India.csv');
    $csv = Reader::createFromPath($csvFile, 'r');
    $csv->setHeaderOffset(0);

    foreach ($csv->getRecords() as $record) {
        DB::table('milk_production')->insert([
            'year' => $record['year'],
            'milk_production' => $record['milk_production'],
            'human_population' => $record['human_population'],
            'per_capita_availability' => $record['per_capita_availability'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
}
