<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        // Optionally associate with the current user/plant manager
        $product = new Product();
        $product->name = $validated['name'];
        $product->quantity = $validated['quantity'];
        $product->created_by = Auth::id(); // if you have such a column
        $product->save();

        return redirect()->back()->with('success', 'Product added!');
    }
} 