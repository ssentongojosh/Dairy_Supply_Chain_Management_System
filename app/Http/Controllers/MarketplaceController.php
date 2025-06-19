<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    /**
     * Show marketplace - products from different sellers
     */
    public function index(Request $request)
    {
        $userRole = Auth::user()->role ?? null;

        // Determine which seller roles the current user can buy from
        $allowedSellerRoles = $this->getAllowedSellerRoles($userRole);

        if (empty($allowedSellerRoles)) {
            return redirect()->back()->with('error', 'You do not have permission to access the marketplace.');
        }

        // Build the query to get products with inventory from allowed sellers
        $query = Inventory::with(['product', 'user'])
            ->whereHas('user', function($q) use ($allowedSellerRoles) {
                $q->whereIn('role', $allowedSellerRoles);
            })
            ->where('quantity', '>', 0) // Only show products in stock
            ->where('selling_price', '>', 0); // Only show products with set prices

        // Apply filters
        if ($request->filled('search')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', $request->max_price);
        }

        if ($request->filled('seller')) {
            $query->where('user_id', $request->seller);
        }

        // Sorting
        $sortBy = $request->get('sort', 'product_name');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('selling_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('selling_price', 'desc');
                break;
            case 'seller':
                $query->orderBy('user_id');
                break;
            default:
                $query->join('products', 'inventories.product_id', '=', 'products.id')
                      ->orderBy('products.name');
        }

        $inventoryItems = $query->paginate(12);

        // Get categories for filter
        $categories = Product::distinct()->pluck('category')->filter();

        // Get sellers for filter
        $sellers = User::whereIn('role', $allowedSellerRoles)
                      ->whereHas('inventory', function($q) {
                          $q->where('quantity', '>', 0)->where('selling_price', '>', 0);
                      })
                      ->select('id', 'name', 'role')
                      ->get();

        return view('marketplace.index', compact('inventoryItems', 'categories', 'sellers', 'userRole'));
    }

    /**
     * Show detailed view of a product with all sellers
     */
    public function showProduct(Product $product, Request $request)
    {
        $userRole = Auth::user()->role ?? null;
        $allowedSellerRoles = $this->getAllowedSellerRoles($userRole);

        if (empty($allowedSellerRoles)) {
            return redirect()->back()->with('error', 'You do not have permission to access the marketplace.');
        }

        // Get all sellers who have this product in stock
        $sellers = Inventory::with(['user'])
            ->whereHas('user', function($q) use ($allowedSellerRoles) {
                $q->whereIn('role', $allowedSellerRoles);
            })
            ->where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->where('selling_price', '>', 0)
            ->orderBy('selling_price', 'asc')
            ->get();

        if ($sellers->isEmpty()) {
            return redirect()->route('marketplace.index')
                           ->with('error', 'This product is not available from any sellers.');
        }

        return view('marketplace.product', compact('product', 'sellers', 'userRole'));
    }

    /**
     * Add product to user's inventory (for selling)
     */
    public function addToInventory(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0.01',
            'reorder_point' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        // Check if user already has this product in inventory
        $existingInventory = Inventory::where('user_id', Auth::id())
                                    ->where('product_id', $validated['product_id'])
                                    ->first();

        if ($existingInventory) {
            // Update existing inventory
            $existingInventory->update([
                'quantity' => $existingInventory->quantity + $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'selling_price' => $validated['selling_price'],
                'reorder_point' => $validated['reorder_point'] ?? $existingInventory->reorder_point,
                'location' => $validated['location'] ?? $existingInventory->location,
                'last_restocked_at' => now(),
            ]);

            $message = 'Product inventory updated successfully!';
        } else {
            // Create new inventory entry
            Inventory::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'selling_price' => $validated['selling_price'],
                'reorder_point' => $validated['reorder_point'] ?? 10,
                'location' => $validated['location'],
                'last_restocked_at' => now(),
            ]);

            $message = 'Product added to your inventory successfully!';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Get allowed seller roles based on user role
     */
    private function getAllowedSellerRoles($userRole)
    {
        switch ($userRole) {
            case 'retailer':
                return ['wholesaler']; // Retailers can only buy from wholesalers
            case 'wholesaler':
                return ['factory', 'supplier']; // Wholesalers can buy from factories and suppliers
            case 'factory':
                return ['supplier']; // Factories can buy from suppliers
            default:
                return []; // No access for other roles
        }
    }

    /**
     * Show form to add product to inventory
     */
    public function showAddForm()
    {
        $products = Product::where('is_active', true)
                          ->orderBy('name')
                          ->get();

        return view('marketplace.add-product', compact('products'));
    }

    /**
     * Create new product (for sellers who want to add completely new products)
     */
    public function createProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'image_url' => 'nullable|url',
            // Inventory fields
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0.01',
            'reorder_point' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Create the product
            $product = Product::create([
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? 'SKU-' . time(),
                'description' => $validated['description'],
                'category' => $validated['category'],
                'supplier_id' => Auth::id(),
                'image_url' => $validated['image_url'],
                'price' => $validated['selling_price'], // Default price
                'is_active' => true,
            ]);

            // Add to user's inventory
            Inventory::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'selling_price' => $validated['selling_price'],
                'reorder_point' => $validated['reorder_point'] ?? 10,
                'location' => $validated['location'],
                'last_restocked_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('marketplace.index')
                           ->with('success', 'New product created and added to your inventory!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Failed to create product. Please try again.')
                           ->withInput();
        }
    }
}
