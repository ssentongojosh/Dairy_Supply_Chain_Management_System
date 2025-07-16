<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Products;
use App\Models\Inventory;

class CheckProductThreshold extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:check-threshold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products below the threshold';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //checks then alerts so task can be carried out
        $lowStock = Product::whereColumn('quantity', '<', 'threshold')->get();

        if ($lowStock->isEmpty()) {
            $this->info('✅ All products are above threshold.');
        } else {
            foreach ($lowStock as $product) {
                $this->warn("⚠️ {$product->name} is below threshold! Current: {$product->quantity}, Threshold: {$product->threshold}");
            }
        }
    }
}
