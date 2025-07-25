<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RawMaterial;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\RawMaterialBatch;
use App\Models\RawMaterialBatchial;
class RawMaterialInventoryController extends Controller
{
    /**
     * Route users to appropriate inventory view based on their role
     */
    public function index()
    {

    }

    //creating a new inventory item
    public function create()
    {
        return view('/inventory.create');
    }

    //store
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        // Create a new Product as a raw material
        $rawMaterial = new \App\Models\Product();
        $rawMaterial->name = $validated['name'];
        $rawMaterial->quantity = $validated['quantity'];
        $rawMaterial->category = 'Raw Milk';
        $rawMaterial->created_by = auth()->id(); // if you have such a column
        $rawMaterial->save();

        return redirect()->back()->with('success', 'Raw material added!');

    $data = $request->validate([
        'name' => 'required|string|max:15',
        'quantity' => 'required|integer|min:1',
        'expiry' => 'required|date',
    ]);

    // Save the raw material
    $newItem = RawMaterial::create($data);

    return redirect()->back()->with('success', 'Item added successfully!');
}



    //show one inventory item
    public function show($id)
    {
        $item = Rawmaterial::findOrFail($id);

         $quantity = $item->quantity;

            if ($quantity <= 100) {
              $status = 'low stock';
              $color = 'red';
            } elseif ($quantity <= 250) {
              $status = 'limited stock';
              $color = 'orange';
            } else {
              $status = 'available';
              $color = 'green';
            }

            // assuming you have a Batch model related to RawMaterial
            $batches = $item->batches()->with('supplier')->get();

            //for the usuage table
            $usages = $item->usages()->orderBy('used_date', 'desc')->get();

        return view('inventory.details', compact('item', 'status', 'color', 'batches', 'usages'));

    }

    //update inventory
    public function update(Request $request, $id)
    {
        $item = RawMaterial::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:15',
            'quantity' => 'required|integer|min:1',
            'expiry' => 'required|date',
        ]);

        $item->update([
            'name'=>$request->name,
            'quantity'=>$request->quantity,
            'expiry'=>$request->expiry,
        ]);

       // return redirect()->back()->with('message','Item updated successfully');
       return redirect()->route('plant_manager.dashboard')->with('message', 'Item updated successfully');

    }

    //delete an inventory
    public function destroy($id)
    {
        $item = RawMaterial::findOrFail($id);
        $item->delete();
        return redirect()->route('inventory.index')->with('success','Inventory item deleted');
    }

    //search for an inventory
    public function search(Request $request)
    {
        $search = $request->input('search');
        $item = RawMaterial::where('name','like','%' .$search. '%')->first();
        if(!$item){
            return redirect()->route('inventory.index')->with('message','No matching item found');
        }
        return view('inventory.details', compact('item'));
    }

    //for continuous update of raw material
    public function updateQuantity(Request $request, $id)
    {
    $item = RawMaterial::findOrFail($id);
    $item->quantity = $request->input('quantity');
    $item->save();

    // Auto-reorder logic
    if ($item->quantity < $item->threshold) {
        // Check if an order for this material already exists and is pending
        $existingOrder = Order::where('raw_material_id', $item->id)
                              ->where('status', 'pending')
                              ->first();

        if (!$existingOrder) {
            // Create new auto-reorder
            Order::create([
                'raw_material_id' => $item->id,
                'quantity' => $item->reorder_quantity ?? 100, // you can decide this
                'status' => 'pending',
                'type' => 'auto', // optional to indicate it's system-generated
                'supplier_id' => $item->supplier_id, // if you track supplier per material
            ]);

            Log::info("Auto-reorder triggered for {$item->name}");
        }
    }

    return redirect()->back()->with('success', 'Stock updated.');
   }



public function storeFromDelivery(Delivery $delivery)
{
    // Create a new batch using delivery data
    $batch = RawMaterialBatch::create([
        'batch_no' => 'BATCH-' . uniqid(),
        'raw_material_id' => $delivery->raw_material_id,
        'quantity' => $delivery->quantity_delivered,
        'unit' => $delivery->unit,
        'date_received' => $delivery->date_delivered,
    ]);

    return response()->json([
        'message' => 'Batch created from delivery.',
        'batch' => $batch
    ]);
}


};
