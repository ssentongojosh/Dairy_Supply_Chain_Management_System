<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetailInventoryController extends Controller
{
    /**
     * Display the retailer's inventory
     */
    public function index(Request $request)
    {
        $query = Inventory::where('user_id', Auth::id())
                          ->with(['product', 'product.supplier'])
                          ->whereHas('product.supplier', function($q) {
                              $q->where('role', \App\Enums\Role::WHOLESALER);
                          });

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

        // Get pending order product IDs for the current user within last 24 hours
        $pendingOrderProductIds = Order::where('buyer_id', Auth::id())
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24))
            ->with('orderItems')
            ->get()
            ->flatMap(function($order) {
                return $order->orderItems->pluck('product_id');
            })
            ->unique()
            ->toArray();

        // Add pending order status to inventory items
        $inventory->each(function($item) use ($pendingOrderProductIds) {
            $item->has_pending_order = in_array($item->product->id, $pendingOrderProductIds);
        });

        // Get categories for filter dropdown
        $categories = Product::select('category')
                            ->whereHas('inventory', function($q) {
                                $q->where('user_id', Auth::id());
                            })
                            ->whereHas('supplier', function($q) {
                                $q->where('role', \App\Enums\Role::WHOLESALER);
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
            'out_of_stock_items' => Inventory::where('user_id', Auth::id())->where('quantity', 0)->count(),
            'total_value' => Inventory::where('user_id', Auth::id())
                                    ->join('products', 'inventories.product_id', '=', 'products.id')
                                    ->sum(DB::raw('inventories.quantity * products.price')),
        ];

        return view('retailer.inventory', compact('inventory', 'stats', 'categories'));
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

        return redirect()->route('retailer.inventory')
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
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if inventory item already exists
        $existingInventory = Inventory::where('user_id', Auth::id())
                                    ->where('product_id', $request->product_id)
                                    ->first();

        if ($existingInventory) {
            // Update existing inventory
            $existingInventory->quantity += $request->quantity;
            $existingInventory->notes = $request->notes;
            $existingInventory->save();

            $productName = $existingInventory->product->name;
            return redirect()->route('retailer.inventory')
                            ->with('success', "Added {$request->quantity} units to existing inventory for {$productName}");
        } else {
            // Create new inventory item
            Inventory::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
            ]);

            $product = Product::find($request->product_id);
            return redirect()->route('retailer.inventory')
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

        return redirect()->route('retailer.inventory')
                        ->with('success', "Removed {$productName} from inventory");
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
                          ->whereHas('supplier', function($q) {
                              $q->where('role', \App\Enums\Role::WHOLESALER);
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

        return redirect()->route('retailer.inventory')
                        ->with('success', 'Reorder threshold updated successfully!');
    }

    /**
     * Bulk update thresholds for all inventory items
     */
    public function bulkUpdateThreshold(Request $request)
    {
        $request->validate([
            'reorder_point' => 'required|integer|min:1',
            'auto_order_quantity' => 'nullable|integer|min:1',
            'overwrite_existing' => 'nullable|boolean',
        ]);

        $query = Inventory::where('user_id', Auth::id());

        if (!$request->overwrite_existing) {
            $query->whereNull('reorder_point');
        }

        $updateData = ['reorder_point' => $request->reorder_point];
        if ($request->auto_order_quantity) {
            $updateData['auto_order_quantity'] = $request->auto_order_quantity;
        }

        $updatedCount = $query->update($updateData);

        return redirect()->route('retailer.inventory')
                        ->with('success', "Thresholds updated for {$updatedCount} items!");
    }

    /**
     * Create reorder for specific inventory item
     */
    public function createReorder(Inventory $inventory)
    {
        // Check if the inventory belongs to the current user
        if ($inventory->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        // Check if item is actually below threshold
        if ($inventory->quantity > $inventory->reorder_point) {
            return response()->json(['success' => false, 'message' => 'Item is not below reorder threshold'], 400);
        }

        // Check for existing pending orders for this product within the last 24 hours
        $existingOrder = Order::where('buyer_id', Auth::id())
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereHas('orderItems', function($query) use ($inventory) {
                $query->where('product_id', $inventory->product->id);
            })
            ->first();

        if ($existingOrder) {
            return response()->json([
                'success' => false,
                'message' => 'A pending order for this product already exists. Please wait for it to be processed.',
                'existing_order_id' => $existingOrder->id
            ], 409);
        }

        // Get the supplier for this product
        $supplier = $inventory->product->supplier;
        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'No supplier found for this product'], 400);
        }

        // Ensure the supplier is a wholesaler
        if ($supplier->role !== \App\Enums\Role::WHOLESALER) {
            return response()->json(['success' => false, 'message' => 'Orders can only be placed with wholesalers. This supplier is not a wholesaler.'], 400);
        }

        // Calculate order quantity
        $orderQuantity = $inventory->auto_order_quantity ?? ($inventory->reorder_point * 3);

        // Create order
        $order = Order::create([
            'buyer_id' => Auth::id(),
            'seller_id' => $supplier->id,
            'status' => 'pending',
            'total_amount' => $orderQuantity * $inventory->product->price,
            'notes' => 'Auto-generated reorder based on threshold',
        ]);

        // Create order item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $inventory->product->id,
            'quantity' => $orderQuantity,
            'unit_price' => $inventory->product->price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reorder created successfully',
            'order_id' => $order->id
        ]);
    }

    /**
     * Create automatic reorders for all items below threshold
     */
    public function autoReorder()
    {
        $lowStockItems = Inventory::where('user_id', Auth::id())
                                 ->whereColumn('quantity', '<=', 'reorder_point')
                                 ->with(['product', 'product.supplier'])
                                 ->get();

        $ordersCreated = 0;
        $skippedItems = 0;

        // Get existing pending orders for this user within the last 24 hours
        $existingOrderProductIds = Order::where('buyer_id', Auth::id())
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24))
            ->with('orderItems')
            ->get()
            ->flatMap(function($order) {
                return $order->orderItems->pluck('product_id');
            })
            ->unique()
            ->toArray();

        // Filter out items that already have pending orders
        $filteredItems = $lowStockItems->filter(function($item) use ($existingOrderProductIds) {
            return !in_array($item->product->id, $existingOrderProductIds);
        });

        if ($filteredItems->isEmpty()) {
            return response()->json([
                'success' => true,
                'orders_created' => 0,
                'skipped_items' => $lowStockItems->count(),
                'message' => 'No new orders created. All low stock items already have pending orders.'
            ]);
        }

        $groupedBySupplier = $filteredItems->groupBy('product.supplier.id');

        foreach ($groupedBySupplier as $supplierId => $items) {
            $supplier = $items->first()->product->supplier;

            if (!$supplier) {
                $skippedItems += $items->count();
                continue;
            }

            // Ensure the supplier is a wholesaler
            if ($supplier->role !== \App\Enums\Role::WHOLESALER) {
                $skippedItems += $items->count();
                continue;
            }

            $totalAmount = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $orderQuantity = $item->auto_order_quantity ?? ($item->reorder_point * 3);
                $itemTotal = $orderQuantity * $item->product->price;
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product_id' => $item->product->id,
                    'quantity' => $orderQuantity,
                    'unit_price' => $item->product->price,
                ];
            }

            // Create order
            $order = Order::create([
                'buyer_id' => Auth::id(),
                'seller_id' => $supplier->id,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'notes' => 'Auto-generated bulk reorder based on thresholds',
            ]);

            // Create order items
            foreach ($orderItems as $orderItemData) {
                $orderItemData['order_id'] = $order->id;
                OrderItem::create($orderItemData);
            }

            $ordersCreated++;
        }

        $totalSkipped = $skippedItems + ($lowStockItems->count() - $filteredItems->count());

        return response()->json([
            'success' => true,
            'orders_created' => $ordersCreated,
            'skipped_items' => $totalSkipped,
            'message' => "Created {$ordersCreated} automatic orders" .
                        ($totalSkipped > 0 ? " ({$totalSkipped} items skipped - already have pending orders)" : "")
        ]);
    }
}
