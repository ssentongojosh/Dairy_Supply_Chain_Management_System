<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;



class ProductInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //can be seen by the dashboard of manager
        $products = Product::all(); 
        return view('plant_manager.dashboard', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:15',
            'quantity' => 'required|integer|min:1',
            'manufacture_date' => 'required|date',
            'price' => 'required|numeric|min:0',
        ]);
        
        $data['supplier_id'] = Auth::id();
        //save and store new item
        $newItem = Products::create($data);

        //new item exists so can now be stored
        return redirect()->back()->with('success', 'Item added successfully!')->with('newItem', $newItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //display a product
        $item = Products::findOrFail($id);

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

        return view('inventory.productdetails', compact('item', 'status', 'color'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Product::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:15',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:10',
            'price' => 'required|numeric|min:0',
        ]);

        $item->update($data);

        return redirect()->back()->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = RawMaterial::findOrFail($id);
        $item->delete();

        return redirect()->route('raw-material.index')->with('success', 'Raw material deleted.');
    }
}
