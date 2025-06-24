<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Route users to appropriate inventory view based on their role
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Normalize role if enum
        $role = $user->role;
        if ($role instanceof \App\Enums\Role) {
            $role = $role->value;
        }

        // Route users based on their role
        switch ($role) {
            case 'retailer':
                // Redirect retailers to their inventory page
                return redirect()->route('retailer.inventory');

            case 'wholesaler':
                // Redirect wholesalers to their inventory page
                return redirect()->route('wholesaler.inventory');

            case 'farmer':
                // Redirect farmers to their inventory page
                return redirect()->route('farmer.inventory');

            case 'plant_manager':
                // Redirect plant managers to their inventory page
                return redirect()->route('plant_manager.inventory');

            case 'factory':
                // TODO: Create factory inventory controller
                return redirect()->route('dashboard')->with('info', 'Factory inventory management coming soon!');

            case 'supplier':
                // Redirect suppliers to their inventory page
                return redirect()->route('supplier.inventory');

            case 'admin':
                // TODO: Create admin inventory overview
                return redirect()->route('dashboard')->with('info', 'Admin inventory overview coming soon!');

            default:
                return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
    }
}

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
