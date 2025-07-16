<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\InventoryThresholdReached;
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
                return redirect()->route('catalog.index');

            case 'wholesaler':
                // Redirect wholesalers to their inventory page
                return redirect()->route('catalog.index');

            case 'farmer':
                // Redirect farmers to their inventory page
                return redirect()->route('inventory.raw_materials');

            case 'plant_manager':
                // Redirect plant managers to their inventory page
                return redirect()->route('plant_manager.inventory');

            case 'factory':
                // TODO: Create factory inventory controller
                return redirect()->route('dashboard')->with('info', 'Factory inventory management coming soon!');

            case 'supplier':
                // Redirect suppliers to their inventory page
                return redirect()->route('delivery.mine');

            case 'admin':
                // TODO: Create admin inventory overview
                return redirect()->route('supplier.inventory')->with('info', 'Admin inventory overview coming soon!');

            default:
                return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
    }

    /**
     * Display the wholesaler's inventory
     */
    public function wholesalerInventory(Request $request)
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
     * Add new inventory item for wholesaler
     */
    public function wholesalerStore(Request $request)
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
     * Update inventory quantity for wholesaler
     */
    public function wholesalerUpdateQuantity(Request $request, Inventory $inventory)
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
     * Update threshold for wholesaler inventory item
     */
    public function wholesalerUpdateThreshold(Request $request, Inventory $inventory)
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

        if ($inventory->current_stock < $inventory->reorder_point) {
        // Dispatch the event if threshold is reached
        InventoryThresholdReached::dispatch($inventory->product_id, $inventory->quantity, $inventory->reorder_point);
    }
        return redirect()->route('wholesaler.inventory')
                        ->with('success', 'Reorder threshold updated successfully!');
    }

    /**
     * Remove inventory item for wholesaler
     */
    public function wholesalerDestroy(Inventory $inventory)
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
     * Get available products for adding to wholesaler inventory
     */
    public function wholesalerGetProducts()
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

