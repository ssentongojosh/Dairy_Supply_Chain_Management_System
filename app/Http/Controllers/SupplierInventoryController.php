<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierInventoryController extends Controller
{    /**
     * Display inventory dashboard for supplier
     */
    public function index(Request $request)
    {
        // Get search and filter parameters
        $search = $request->get('search');
        $category = $request->get('category');
        $stockStatus = $request->get('stock_status');
        $sortBy = $request->get('sort_by', 'name');

        // Build the query
        $query = Inventory::where('user_id', Auth::id())
                         ->with('product');

        // Apply search filter
        if ($search) {
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if ($category) {
            $query->whereHas('product', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        // Apply stock status filter
        if ($stockStatus) {
            switch ($stockStatus) {
                case 'out_of_stock':
                    $query->where('quantity', 0);
                    break;
                case 'low_stock':
                    $query->whereRaw('quantity <= threshold AND quantity > 0');
                    break;
                case 'in_stock':
                    $query->whereRaw('quantity > threshold');
                    break;
            }
        }

        // Apply sorting
        switch ($sortBy) {
            case 'quantity':
                $query->orderBy('quantity', 'desc');
                break;
            case 'price':
                $query->orderBy('unit_price', 'desc');
                break;
            case 'updated_at':
                $query->orderBy('updated_at', 'desc');
                break;
            default:
                $query->join('products', 'inventories.product_id', '=', 'products.id')
                      ->orderBy('products.name');
                break;
        }

        // Get paginated results
        $inventory = $query->paginate(15);

        // Get all inventory items for statistics (without pagination)
        $allInventoryItems = Inventory::where('user_id', Auth::id())
                                   ->with('product')
                                   ->get();

        // Inventory statistics
        $inventoryStats = [
            'total_products' => $allInventoryItems->count(),
            'low_stock_products' => $allInventoryItems->filter(function($item) {
                return $item->quantity <= $item->threshold && $item->quantity > 0;
            })->count(),
            'out_of_stock_products' => $allInventoryItems->where('quantity', 0)->count(),
            'total_value' => $allInventoryItems->sum(function($item) {
                return $item->quantity * $item->unit_price;
            }),
        ];        // Get available categories for filter dropdown
        $categories = Product::whereHas('inventory', function($q) {
                            $q->where('user_id', Auth::id());
                        })
                        ->distinct()
                        ->pluck('category')
                        ->filter()
                        ->sort()
                        ->values();

        return view('supplier.inventory', compact(
            'inventory',
            'inventoryStats',
            'categories'
        ));
    }    /**
     * Store new inventory item
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'threshold' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::transaction(function() use ($request) {
                // Create product first
                $product = Product::create([
                    'name' => $request->product_name,
                    'category' => $request->category,
                    'sku' => $request->sku,
                    'unit' => $request->unit ?? 'pcs',
                    'description' => $request->description,
                ]);

                // Handle image upload if provided
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('products', 'public');
                    $product->update(['image' => $imagePath]);
                }

                // Check if this product already exists in supplier's inventory
                $existingInventory = Inventory::where('user_id', Auth::id())
                                           ->where('product_id', $product->id)
                                           ->first();

                if ($existingInventory) {
                    throw new \Exception('This product already exists in your inventory.');
                }

                // Create inventory item
                Inventory::create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'threshold' => $request->threshold,
                ]);
            });            return response()->json([
                'success' => true,
                'message' => 'Product added to inventory successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update inventory quantity
     */
    public function updateQuantity(Request $request, Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        try {
            $inventory->update([
                'quantity' => $request->quantity,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity'
            ], 500);
        }
    }

    /**
     * Update inventory threshold
     */
    public function updateThreshold(Request $request, Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'threshold' => 'required|numeric|min:0',
        ]);

        try {
            $inventory->update([
                'threshold' => $request->threshold,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Threshold updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update threshold'
            ], 500);
        }
    }

    /**
     * Delete inventory item
     */
    public function destroy(Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $inventory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from inventory successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product from inventory'
            ], 500);
        }
    }

    /**
     * Get available products for adding to inventory
     */
    public function getAvailableProducts()
    {
        // Get products that are not already in supplier's inventory
        $existingProductIds = Inventory::where('user_id', Auth::id())
                                    ->pluck('product_id')
                                    ->toArray();

        $availableProducts = Product::whereNotIn('id', $existingProductIds)
                                  ->orderBy('name')
                                  ->get(['id', 'name', 'category', 'unit']);

        return response()->json($availableProducts);
    }

    /**
     * Bulk update thresholds
     */
    public function bulkUpdateThreshold(Request $request)
    {
        $request->validate([
            'threshold' => 'required|numeric|min:0',
            'inventory_ids' => 'required|array',
            'inventory_ids.*' => 'exists:inventories,id'
        ]);

        try {
            Inventory::where('user_id', Auth::id())
                   ->whereIn('id', $request->inventory_ids)
                   ->update(['threshold' => $request->threshold]);

            return response()->json([
                'success' => true,
                'message' => 'Thresholds updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update thresholds'
            ], 500);
        }
    }

    /**
     * Get inventory statistics
     */
    public function getStats()
    {
        $inventoryItems = Inventory::where('user_id', Auth::id())->get();

        $stats = [
            'total_products' => $inventoryItems->count(),
            'low_stock_items' => $inventoryItems->where('quantity', '<=', 'threshold')->count(),
            'out_of_stock' => $inventoryItems->where('quantity', 0)->count(),
            'total_value' => $inventoryItems->sum(function($item) {
                return $item->quantity * $item->unit_price;
            }),
        ];

        return response()->json($stats);
    }

    /**
     * Show specific inventory item
     */
    public function show(Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $inventory->load('product');

        return response()->json([
            'success' => true,
            'inventory' => $inventory
        ]);
    }

    /**
     * Update inventory item
     */
    public function update(Request $request, Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'threshold' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function() use ($request, $inventory) {
                // Update product information
                $inventory->product->update([
                    'name' => $request->product_name,
                    'category' => $request->category,
                    'sku' => $request->sku,
                    'unit' => $request->unit ?? 'pcs',
                    'description' => $request->description,
                ]);

                // Update inventory information
                $inventory->update([
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'threshold' => $request->threshold,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product'
            ], 500);
        }
    }

    /**
     * Adjust stock quantity
     */
    public function adjustStock(Request $request, Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            $newQuantity = 0;
            $oldQuantity = $inventory->quantity;

            switch ($request->adjustment_type) {
                case 'add':
                    $newQuantity = $oldQuantity + $request->quantity;
                    break;
                case 'subtract':
                    $newQuantity = max(0, $oldQuantity - $request->quantity);
                    break;
                case 'set':
                    $newQuantity = $request->quantity;
                    break;
            }

            $inventory->update(['quantity' => $newQuantity]);

            // Log the adjustment (you might want to create a stock_adjustments table)
            // For now, we'll just return success

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stock'
            ], 500);
        }
    }

    /**
     * Bulk import products from CSV
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);

            $imported = 0;
            $errors = [];

            foreach ($csvData as $row) {
                if (count($row) !== count($header)) {
                    continue; // Skip malformed rows
                }

                $data = array_combine($header, $row);

                try {
                    // Create or get product
                    $product = Product::firstOrCreate([
                        'name' => $data['name'] ?? '',
                        'sku' => $data['sku'] ?? null,
                    ], [
                        'category' => $data['category'] ?? '',
                        'unit' => $data['unit'] ?? 'pcs',
                        'description' => $data['description'] ?? '',
                    ]);

                    // Create inventory item
                    Inventory::updateOrCreate([
                        'user_id' => Auth::id(),
                        'product_id' => $product->id,
                    ], [
                        'quantity' => $data['quantity'] ?? 0,
                        'unit_price' => $data['unit_price'] ?? 0,
                        'threshold' => $data['threshold'] ?? 10,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row with product '{$data['name']}': " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} products" . (count($errors) > 0 ? " with " . count($errors) . " errors" : ""),
                'imported' => $imported,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download CSV template for bulk import
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'category', 'sku', 'unit', 'quantity', 'unit_price', 'threshold', 'description']);
            fputcsv($file, ['Sample Product', 'Seeds', 'SEED001', 'kg', '100', '50.00', '10', 'Sample product description']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
