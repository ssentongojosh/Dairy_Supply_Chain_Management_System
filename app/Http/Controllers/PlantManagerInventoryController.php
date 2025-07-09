<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlantManagerInventoryController extends Controller
{
    /**
     * Display the plant manager's inventory (raw materials and finished products)
     */
    public function index()
    {
        // Get all finished products from the database
        $products = Product::all();

        // Get all raw materials from the database
        $rawMaterials = RawMaterial::all();

        //count for the low stock
        $lowStockProducts = Product::where('quantity', '<=', 150)->count();
        $lowStockRawMaterials = RawMaterial::where('quantity', '<=', 150)->count();

        //total for low stock
        $totalLowStock = $lowStockProducts + $lowStockRawMaterials;

        //get count for today deliveries
        $today = Carbon::today();
        $todayDeliveriesCount = Delivery::whereDate('created_at', $today)->count();

        // Pass the data to the Blade view
        return view('plant_manager.dashboard', compact('products', 'rawMaterials', 'totalLowStock','todayDeliveriesCount'));
    }

    /**
     * Store a new product
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'manufacture_date' => 'required|date'
        ]);

        // Create inventory entry for the product
        $product = Product::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'category' => 'Finished Product',
            'supplier_id' => Auth::id(),
            'added_on' => $validated['manufacture_date']
        ]);

        // Create corresponding inventory entry
        Inventory::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'selling_price' => $validated['price'],
            'last_restocked_at' => now()
        ]);

        return redirect()->back()->with('success', 'Product added successfully!');
    }

    /**
     * Show a specific product
     */
    public function showProduct(Product $product)
    {
        $inventory = Inventory::where('product_id', $product->id)
                             ->where('user_id', Auth::id())
                             ->first();

        return view('plant_manager.product-details', compact('product', 'inventory'));
    }

    /**
     * Store a new raw material
     */
    public function storeRawMaterial(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'expiry' => 'required|date'
        ]);

        // Add user_id to track which plant manager added this raw material
        $validated['user_id'] = Auth::id();

        RawMaterial::create($validated);

        return redirect()->back()->with('success', 'Raw material added successfully!');
    }

    /**
     * Show a specific raw material
     */
    public function showRawMaterial(RawMaterial $rawMaterial)
    {
        return view('plant_manager.raw-material-details', compact('rawMaterial'));
    }

    /**
     * Search inventory items
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $inventoryItems = Inventory::where('user_id', Auth::id())
                                 ->with('product')
                                 ->when($query, function($q) use ($query) {
                                     $q->whereHas('product', function($productQuery) use ($query) {
                                         $productQuery->where('name', 'like', "%{$query}%");
                                     });
                                 })
                                 ->get();

        return view('plant_manager.inventory-search', compact('inventoryItems', 'query'));
    }
}
