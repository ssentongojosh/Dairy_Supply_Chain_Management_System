<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessCustomer;
use Illuminate\Support\Facades\DB;
class BusinessCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $file = fopen(database_path('seeders/Dataset/customer_segmentation_data_business.csv'), 'r');
    $header = fgetcsv($file);
     while ($row = fgetcsv($file)) {
        $data = array_combine($header, $row);
        BusinessCustomer::create($data);
    }
    fclose($file);
}
}
