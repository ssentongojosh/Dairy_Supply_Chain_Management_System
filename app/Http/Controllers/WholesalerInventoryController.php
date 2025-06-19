<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WholesalerInventoryController extends Controller
{
    /**
     * Display the wholesaler's inventory
     */
    public function index(Request $request)
    {
        $query = Inventory::where('user_id', Auth::id())
                          ->with(['product']);

        // Apply search filter
        if ($request->filled('search')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        // Apply stock level filter
        if ($request->filled('stock_level')) {
            switch ($request->stock_level) {
                case 'low':
                    $query->where('quantity', '<=', 10);
                    break;
                case 'medium':
                    $query->whereBetween('quantity', [11, 50]);
                    break;
                case 'high':
                    $query->where('quantity', '>', 50);
                    break;
                case 'out':
                    $query->where('quantity', 0);
                    break;
            }
        }

        $inventory = $query->orderBy('updated_at', 'desc')->paginate(15);

        // Get categories for filter dropdown
        $categories = Product::select('category')
                            ->whereHas('inventory', function($q) {
                                $q->where('user_id', Auth::id());
                            })
                            ->distinct()
                            ->pluck('category')
                            ->filter();

        // Calculate statistics
        $stats = [
            'total_items' => Inventory::where('user_id', Auth::id())->count(),
            'total_quantity' => Inventory::where('user_id', Auth::id())->sum('quantity'),
            'low_stock_items' => Inventory::where('user_id', Auth::id())->whereColumn('quantity', '<=', 'reorder_point')->count(),
            'out_of_stock' => Inventory::where('user_id', Auth::id())->where('quantity', 0)->count(),
            'total_value' => Inventory::where('user_id', Auth::id())
                                    ->join('products', 'inventories.product_id', '=', 'products.id')
                                    ->sum(DB::raw('inventories.quantity * products.price')),
        ];

        return view('wholesaler.inventory', compact('inventory', 'stats', 'categories'));
    }

    /**
     * Update inventory quantity
     */
    public function updateQuantity(Request $request, Inventory $inventory)
    {
        // Check if the inventory belongs to the current user
        if ($inventory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to inventory item');
        }

        $request->validate([
            'quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldQuantity = $inventory->quantity;
        $inventory->update([
            'quantity' => $request->quantity,
            'notes' => $request->notes,
        ]);

        $action = $request->quantity > $oldQuantity ? 'increased' : 'decreased';
        $difference = abs($request->quantity - $oldQuantity);

        return redirect()->route('wholesaler.inventory')
                        ->with('success', "Inventory {$action} by {$difference} units for {$inventory->product->name}");
    }

    /**
     * Add new inventory item
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if inventory item already exists
        $existingInventory = Inventory::where('user_id', Auth::id())
                                    ->where('product_id', $request->product_id)
                                    ->first();

        if ($existingInventory) {
            // Update existing inventory
            $existingInventory->quantity += $request->quantity;
            $existingInventory->unit_cost = $request->unit_cost;
            $existingInventory->selling_price = $request->selling_price;
            $existingInventory->notes = $request->notes;
            $existingInventory->save();

            $productName = $existingInventory->product->name;
            return redirect()->route('wholesaler.inventory')
                            ->with('success', "Added {$request->quantity} units to existing inventory for {$productName}");
        } else {
            // Create new inventory item
            Inventory::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit_cost' => $request->unit_cost,
                'selling_price' => $request->selling_price,
                'notes' => $request->notes,
            ]);

            $product = Product::find($request->product_id);
            return redirect()->route('wholesaler.inventory')
                            ->with('success', "Added new inventory item: {$product->name} with {$request->quantity} units");
        }
    }

    /**
     * Remove inventory item
     */
    public function destroy(Inventory $inventory)
    {
        // Check if the inventory belongs to the current user
        if ($inventory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to inventory item');
        }

        $productName = $inventory->product->name;
        $inventory->delete();

        return redirect()->route('wholesaler.inventory')
                        ->with('success', "Removed {$productName} from inventory");
    }

    /**
     * Update threshold for inventory item
     */
    public function updateThreshold(Request $request, Inventory $inventory)
    {
        // Check if the inventory belongs to the current user
        if ($inventory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to inventory item');
        }

        $request->validate([
            'reorder_point' => 'required|integer|min:1',
            'auto_order_quantity' => 'nullable|integer|min:1',
        ]);

        $inventory->update([
            'reorder_point' => $request->reorder_point,
            'auto_order_quantity' => $request->auto_order_quantity,
        ]);

        return redirect()->route('wholesaler.inventory')
                        ->with('success', 'Reorder threshold updated successfully!');
    }

    /**
     * Get available products for adding to inventory
     */
    public function getAvailableProducts()
    {
        $products = Product::select('id', 'name', 'price', 'supplier_id')
                          ->with(['supplier:id,name,role'])
                          ->whereDoesntHave('inventories', function($q) {
                              $q->where('user_id', Auth::id());
                          })
                          ->orderBy('name')
                          ->get()
                          ->map(function($product) {
                              return [
                                  'id' => $product->id,
                                  'name' => $product->name,
                                  'price' => $product->price,
                                  'supplier_name' => $product->supplier->name ?? 'Unknown',
                              ];
                          });

        return response()->json($products);
    }
}
