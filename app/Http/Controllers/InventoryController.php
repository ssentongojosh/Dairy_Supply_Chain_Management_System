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
        return view('inventory.index', compact('inventory'));
    }

    //creating a new inventory item
    public function create()
    {
        return view('/inventory.create');
    }
    
    //add new inventory
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:15',
            'quantity' => 'required|integer|min:1',
            'unit' => 'string|max:5',
            //'status' => 'in:available,reserved,expired,out_of_stock',
        ]);

        Inventory::create($data);
        return redirect()->route('inventory.create')->with('message', 'Inventory item created successfully!');
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
            'name' => 'required|string|max:15',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:10',
            //'status' => 'required|in:available,limited,reserved,out_of_stock',
        ]);

        $inventory->update([
            'name'=>$request->name,
            'quantity'=>$request->quantity,
            'unit'=>$request->unit,
            //'status'=>$request->status,
        ]);

        return redirect()->route('inventory.search', ['search' => $inventory->name])->with('message','Item updated successfully');
    }

    //delete an inventory
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();
        return response()->json(['message' => 'Inventory item deleted']);
    }

    //search for an inventory
    public function search(Request $request)
    {
        $search = $request->input('search');
        $item = Inventory::where('name','like','%' .$search. '%')->first();
        if(!$item){
            return redirect()->route('inventory.index')->with('message','No matching item found');
        }
        return view('inventory.details', compact('item'));
    }

}
