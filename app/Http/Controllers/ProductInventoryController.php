<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Production;
use App\Models\ProductUsage;
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
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'manufacture_date' => 'required|date',
            //'price' => 'required|numeric|min:0',
        ]);

        //log in the production batch
         $production = Production::create([
        'product_id' => $data['product_id'],
        'quantity' => $data['quantity'],
        'production_date' => $data['production_date'],
        //'status' => 'done', // optional
        ]);

        //increase stock in the products table
        $product = Products::find($data['product_id']);
        $product->increment('quantity', $data['quantity']);

        
        //new item exists so can now be stored
        return redirect()->back()->with('success', 'Inventory updated successfully!');
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

            //for the production model
            $batches = Production::where('product_id', $item->id)->orderByDesc('production_date')->get();

            //for productusage model
            $usages = ProductUsage::where('product_id', $item->id)->orderByDesc('used_on')->get();

        return view('inventory.productdetails', compact('item', 'status', 'color', 'batches', 'usages'));
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

    //for stuff below threshold
    public function lowStock()
    {
    $lowStock = Product::whereColumn('quantity', '<', 'threshold')->get();

    return view('plant_manager.low_stock_products', compact('lowStock'));
    }

}
