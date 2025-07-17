<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RawMaterial;
use App\Models\Order;
use App\Models\Inventory;

class CheckRawMaterialThresholds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rawmaterials:check-thresholds';
    protected $description = 'Check raw materials and auto-reorder if below threshold';

    /**
     * The console command description.
     *
     * @var string
     */
    //protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //command logic
        $materials = RawMaterial::all();

        foreach ($materials as $material) {
            if ($material->quantity < $material->threshold) {
                $existingOrder = Order::where('raw_material_id', $material->id)
                    ->where('status', 'pending')
                    ->first();

                if (!$existingOrder) {
                    Order::create([
                        'raw_material_id' => $material->id,
                        'quantity' => $material->reorder_quantity ?? 100,
                        'status' => 'pending',
                        'type' => 'auto',
                        'supplier_id' => $material->supplier_id,
                    ]);

                    $this->info("Auto-reorder placed for: {$material->name}");
                }
            }
        }
    }
}
