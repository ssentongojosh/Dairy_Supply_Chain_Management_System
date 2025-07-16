<?php

namespace App\Http\Controllers;

use App\Models\ProductItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductItemController extends Controller
{
    /**
     * Store a new product item for the authenticated user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:manufacture_date',
            'batch_number' => 'nullable|string|max:255',
        ]);

        // Check if user already has this product
        $existingItem = ProductItem::where('user_id', Auth::id())
                                  ->where('product_id', $validated['product_id'])
                                  ->where('batch_number', $validated['batch_number'])
                                  ->first();

        if ($existingItem) {
            // Update existing item quantity
            $existingItem->increment('quantity', $validated['quantity']);
            $message = 'Product quantity updated successfully!';
        } else {
            // Create new product item
            ProductItem::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'cost_price' => $validated['cost_price'],
                'selling_price' => $validated['selling_price'],
                'minimum_stock' => $validated['minimum_stock'],
                'manufacture_date' => $validated['manufacture_date'],
                'expiry_date' => $validated['expiry_date'],
                'batch_number' => $validated['batch_number'],
                'status' => 'active'
            ]);
            $message = 'Product added to inventory successfully!';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update a product item
     */
    public function update(Request $request, ProductItem $productItem)
    {
        // Ensure user owns this product item
        if ($productItem->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'status' => 'required|in:active,expired,damaged,sold',
        ]);

        $productItem->update($validated);

        return redirect()->back()->with('success', 'Product updated successfully!');
    }

    /**
     * Delete a product item
     */
    public function destroy(ProductItem $productItem)
    {
        // Ensure user owns this product item
        if ($productItem->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $productItem->delete();

        return redirect()->back()->with('success', 'Product removed from inventory!');
    }
}
