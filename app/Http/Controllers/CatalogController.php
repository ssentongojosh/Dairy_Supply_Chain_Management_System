<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;


class CatalogController extends Controller
{
    //shows only goods which are available and limited but not out of stock
    public function index()
    {
        // shows only goods which are available and limited but not out of stock
        //$products = Product::whereIn('status', ['available', 'limited'])->get();
        //return view('catalog.index', compact('products'));
        // Get all products
    $allProducts = Product::all();

    // Filter by calculated "status"
    $filtered = $allProducts->filter(function ($product) {
        $quantity = $product->quantity;
        return $quantity > 0; // Only include products that are not out of stock
    });

    return view('catalog.index', ['products' => $filtered]);
    }
}
