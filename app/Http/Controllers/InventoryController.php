<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    //showing availabity in inventory
       public function index()
    {
        $inventory = Inventory::all();
        return response()->json($inventory);
    }

    //add new inventory
     public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'delivery_id' => 'required|integer',
            'quantity' => 'required|integer',
            'location' => 'required|string|max:15',
            'goods_type' => 'required|string|max:10',
            'store_id' => 'required|integer',
            'batch_id' => 'required|integer',
            'storage_condition' => 'required|numeric',
            'expiry_date' => 'required|date',
            'status' => 'in:available,reserved,expired,out_of_stock'
        ]);

        $inventory = Inventory::create($validated);
        return response()->json($inventory, 201);
    }

    //show one inventory item
      public function show($id)
    {
        $inventory = Inventory::findOrFail($id);
        return response()->json($inventory);
    }

    //update inventory
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'nullable|integer',
            'status' => 'nullable|in:available,reserved,expired,out_of_stock',
        ]);

        $inventory->update($validated);
        return response()->json($inventory);
    }

    //delete an inventory
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();
        return response()->json(['message' => 'Inventory item deleted']);
    }
    
}
