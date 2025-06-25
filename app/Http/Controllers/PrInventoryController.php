<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory;

class PrInventoryController extends Controller
{
    /**
     * Route users to appropriate inventory view based on their role
     */
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
        ]);

        $inventory->update([
            'name'=>$request->name,
            'quantity'=>$request->quantity,
            'unit'=>$request->unit,
        ]);

        return redirect()->route('inventory.search', ['search' => $inventory->name])->with('message','Item updated successfully');
    }

    //delete an inventory
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success','Inventory item deleted');
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
