<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class UpdateRawMaterialPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rawmaterials:update-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add price column to raw materials and update with random prices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('Starting raw materials price update...');

        // Run the migration
        $this->info('Running migration to add price column...');
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_23_222423_add_price_to_raw_materials_table.php']);
        $this->info(Artisan::output());

        // Run the seeder
        $this->info('Running seeder to update prices...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RawMaterialPriceSeeder']);
        $this->info(Artisan::output());

        // Alternatively, you can use direct DB queries for simpler implementation
        if ($this->confirm('Would you like to use direct DB query to update prices instead?')) {
            $this->info('Updating prices with direct DB query...');
            $count = DB::table('raw_materials')
                ->update([
                    'price' => DB::raw('FLOOR(RAND() * (3000 - 1000 + 1) + 1000)')
                ]);
            $this->info("Updated {$count} raw materials with random prices using direct query.");
        }

        $this->info('Raw materials price update completed successfully!');

        return Command::SUCCESS;
    }

    }

