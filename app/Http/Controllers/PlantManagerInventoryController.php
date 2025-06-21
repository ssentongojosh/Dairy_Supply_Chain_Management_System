<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantManagerInventoryController extends Controller
{
    /**
     * Display the plant manager's inventory (raw materials and finished products)
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

        // Apply stock level filter (adjusted for plant operations)
        if ($request->filled('stock_level')) {
            switch ($request->stock_level) {
                case 'low':
                    $query->where('quantity', '<=', 100); // Low stock for plant
                    break;
                case 'medium':
                    $query->whereBetween('quantity', [101, 500]);
                    break;
                case 'high':
                    $query->where('quantity', '>', 500);
                    break;
                case 'out':
                    $query->where('quantity', 0);
                    break;
            }
        }

        // Apply product type filter
        if ($request->filled('product_type')) {
            switch ($request->product_type) {
                case 'raw_materials':
                    $query->whereHas('product', function($q) {
                        $q->whereIn('category', ['Raw Milk', 'Additives', 'Packaging']);
                    });
                    break;
                case 'finished_products':
                    $query->whereHas('product', function($q) {
                        $q->whereIn('category', ['Pasteurized Milk', 'Yogurt', 'Cheese', 'Butter', 'Cream']);
                    });
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
            'low_stock_items' => Inventory::where('user_id', Auth::id())->where('quantity', '<=', 100)->count(),
            'out_of_stock' => Inventory::where('user_id', Auth::id())->where('quantity', 0)->count(),
            'raw_materials_count' => Inventory::where('user_id', Auth::id())
                                            ->whereHas('product', function($q) {
                                                $q->whereIn('category', ['Raw Milk', 'Additives', 'Packaging']);
                                            })
                                            ->count(),
            'finished_products_count' => Inventory::where('user_id', Auth::id())
                                               ->whereHas('product', function($q) {
                                                   $q->whereIn('category', ['Pasteurized Milk', 'Yogurt', 'Cheese', 'Butter', 'Cream']);
                                               })
                                               ->count(),
            'total_value' => Inventory::where('user_id', Auth::id())
                                    ->join('products', 'inventories.product_id', '=', 'products.id')
                                    ->sum(DB::raw('inventories.quantity * products.price')),
        ];

        return view('plant_manager.inventory', compact('inventory', 'stats', 'categories'));
    }

    /**
     * Update inventory quantity (for production input/output tracking)
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
            'batch_number' => 'nullable|string|max:50',
        ]);

        $oldQuantity = $inventory->quantity;
        $inventory->update([
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'batch_number' => $request->batch_number ?? $inventory->batch_number,
        ]);

        $action = $request->quantity > $oldQuantity ? 'increased' : 'decreased';
        $difference = abs($request->quantity - $oldQuantity);

        return redirect()->route('plant_manager.inventory')
                        ->with('success', "Inventory {$action} by {$difference} units for {$inventory->product->name}");
    }

    /**
     * Add new inventory item (raw materials or finished products)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0.01',
            'batch_number' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date|after:today',
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
            $existingInventory->batch_number = $request->batch_number;
            $existingInventory->expiry_date = $request->expiry_date;
            $existingInventory->notes = $request->notes;
            $existingInventory->save();

            $productName = $existingInventory->product->name;
            return redirect()->route('plant_manager.inventory')
                            ->with('success', "Added {$request->quantity} units to existing inventory for {$productName}");
        } else {
            // Create new inventory item
            Inventory::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit_cost' => $request->unit_cost,
                'selling_price' => $request->selling_price,
                'batch_number' => $request->batch_number,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
                'last_restocked_at' => now(),
            ]);

            $product = Product::find($request->product_id);
            return redirect()->route('plant_manager.inventory')
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

        return redirect()->route('plant_manager.inventory')
                        ->with('success', "Removed {$productName} from inventory");
    }

    /**
     * Update reorder threshold for inventory item
     */
    public function updateThreshold(Request $request, Inventory $inventory)
    {
        // Check if the inventory belongs to the current user
        if ($inventory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to inventory item');
        }

        $request->validate([
            'reorder_point' => 'required|integer|min:1',
        ]);

        $inventory->update([
            'reorder_point' => $request->reorder_point,
        ]);

        return redirect()->route('plant_manager.inventory')
                        ->with('success', 'Reorder threshold updated successfully!');
    }

    /**
     * Get available products for adding to inventory
     */
    public function getAvailableProducts()
    {
        $products = Product::select('id', 'name', 'price', 'category', 'supplier_id')
                          ->with(['supplier:id,name,role'])
                          ->whereDoesntHave('inventories', function($q) {
                              $q->where('user_id', Auth::id());
                          })
                          ->orderBy('category')
                          ->orderBy('name')
                          ->get()
                          ->map(function($product) {
                              return [
                                  'id' => $product->id,
                                  'name' => $product->name,
                                  'price' => $product->price,
                                  'category' => $product->category,
                                  'supplier_name' => $product->supplier->name ?? 'Internal',
                              ];
                          });

        return response()->json($products);
    }

    /**
     * Process raw materials into finished products
     */
    public function processProduction(Request $request)
    {
        $request->validate([
            'raw_material_id' => 'required|exists:inventories,id',
            'raw_quantity' => 'required|integer|min:1',
            'finished_product_id' => 'required|exists:products,id',
            'finished_quantity' => 'required|integer|min:1',
            'batch_number' => 'required|string|max:50',
            'production_notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            // Reduce raw material inventory
            $rawMaterial = Inventory::findOrFail($request->raw_material_id);
            if ($rawMaterial->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access');
            }

            if ($rawMaterial->quantity < $request->raw_quantity) {
                throw new \Exception('Insufficient raw material quantity');
            }

            $rawMaterial->quantity -= $request->raw_quantity;
            $rawMaterial->save();

            // Add to finished products inventory
            $finishedProduct = Inventory::firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'product_id' => $request->finished_product_id,
                ],
                [
                    'quantity' => 0,
                    'unit_cost' => 0,
                    'selling_price' => 0,
                ]
            );

            $finishedProduct->quantity += $request->finished_quantity;
            $finishedProduct->batch_number = $request->batch_number;
            $finishedProduct->notes = $request->production_notes;
            $finishedProduct->last_restocked_at = now();
            $finishedProduct->save();
        });

        return redirect()->route('plant_manager.inventory')
                        ->with('success', 'Production process completed successfully!');
    }
}
