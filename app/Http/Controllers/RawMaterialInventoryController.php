<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RawMaterial;
use App\Models\Inventory;

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

            if ($quantity <= 150) {
              $status = 'out of stock';
              $color = 'red';
            } elseif ($quantity <= 350) {
              $status = 'limited';
              $color = 'orange';
            } else {
              $status = 'available';
              $color = 'green';
            }

        return view('inventory.details', compact('item', 'status', 'color'));
        
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

};   
