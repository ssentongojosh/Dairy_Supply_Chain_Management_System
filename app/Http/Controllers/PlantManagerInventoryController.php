<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantManagerInventoryController extends Controller
{
    /**
     * Display the plant manager's inventory (raw materials and finished products)
     */
    public function index()
    {
        // ✅ Get all finished products from the database
        $products = Product::all();

        // ✅ Get all raw materials from the database
        $rawMaterials = RawMaterial::all();

        //count for the low stock
        $lowStockProducts = Product::where('quantity', '<=', 150)->count();
        $lowStockRawMaterials = RawMaterial::where('quantity', '<=', 150)->count();

        //total for low stock
        $totalLowStock = $lowStockProducts + $lowStockRawMaterials;

        // ✅ Pass the data to the Blade view
        return view('plant_manager.dashboard', compact('products', 'rawMaterials', 'totalLowStock'));
    }
}