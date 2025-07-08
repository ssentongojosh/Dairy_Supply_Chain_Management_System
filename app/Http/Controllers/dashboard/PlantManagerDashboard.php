<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantManagerDashboard extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get products from inventory - the view expects the inventory items themselves
        // The view accesses properties like $product->name, $product->quantity, $product->price
        $products = Inventory::where('user_id', Auth::id())
                           ->with('product')
                           ->whereHas('product', function($query) {
                               $query->whereIn('category', ['Pasteurized Milk', 'Yogurt', 'Cheese', 'Butter']);
                           })
                           ->get();

        // For the view, we need to transform the data so it can access name, quantity, price directly
        $products = $products->map(function($inventory) {
            return (object) [
                'id' => $inventory->id,
                'name' => $inventory->product->name ?? $inventory->name,
                'quantity' => $inventory->quantity,
                'price' => $inventory->selling_price ?? $inventory->product->price ?? 0,
                'manufacture_date' => $inventory->product->added_on ?? $inventory->last_restocked_at,
                'product_id' => $inventory->product_id
            ];
        });

        // Raw materials from the separate RawMaterial model - filtered by user
        $rawMaterials = RawMaterial::where('user_id', Auth::id())->get();

        // Low stock count from Inventory table
        $totalLowStock = Inventory::where('user_id', Auth::id())
                               ->where('quantity', '<=', 150)
                               ->count();

        // Stats for delivery activity
        $stats = [
            'incoming_deliveries' => Order::where('buyer_id', Auth::id())
                                          ->where('status', 'shipped')
                                          ->count(),
            'outgoing_deliveries' => Order::where('seller_id', Auth::id())
                                          ->where('status', 'shipped')
                                          ->count(),
        ];

        // Production and Processing Statistics
        $productionStats = [
            'daily_capacity' => 5000, // liters per day
            'current_production' => $this->getTodayProduction(),
            'efficiency_rate' => 85, // percentage
            'quality_score' => 94, // percentage
        ];

        // Inventory Statistics (raw materials and finished products)
        $inventoryStats = [
            'raw_milk_stock' => Inventory::where('user_id', Auth::id())
                                      ->whereHas('product', function($q) {
                                          $q->where('category', 'Raw Milk');
                                      })
                                      ->sum('quantity'),
            'finished_products' => Inventory::where('user_id', Auth::id())
                                          ->whereHas('product', function($q) {
                                              $q->whereIn('category', ['Pasteurized Milk', 'Yogurt', 'Cheese', 'Butter']);
                                          })
                                          ->count(),
            'low_stock_items' => Inventory::where('user_id', Auth::id())
                                        ->where('quantity', '<=', 100)
                                        ->count(),
            'total_inventory_value' => Inventory::where('user_id', Auth::id())
                                              ->join('products', 'inventories.product_id', '=', 'products.id')
                                              ->sum(DB::raw('inventories.quantity * products.price')),
        ];

        // Recent Orders (incoming from distributors/retailers)
        $recentOrders = Order::where('seller_id', Auth::id())
                            ->with(['buyer', 'items.product'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();

        // Order Statistics
        $orderStats = [
            'pending_orders' => Order::where('seller_id', Auth::id())
                                    ->where('status', 'pending')
                                    ->count(),
            'processing_orders' => Order::where('seller_id', Auth::id())
                                       ->whereIn('status', ['approved', 'processing'])
                                       ->count(),
            'completed_today' => Order::where('seller_id', Auth::id())
                                     ->where('status', 'delivered')
                                     ->whereDate('updated_at', today())
                                     ->count(),
            'total_revenue_month' => Order::where('seller_id', Auth::id())
                                         ->where('status', 'delivered')
                                         ->whereMonth('created_at', now()->month)
                                         ->sum('total_amount'),
        ];

        // Quality Control Alerts
        $qualityAlerts = [
            'temperature_alerts' => 2,
            'batch_tests_pending' => 5,
            'expired_products' => 1,
            'compliance_checks' => 3,
        ];

        // Production Line Status
        $productionLines = [
            [
                'name' => 'Pasteurization Line 1',
                'status' => 'active',
                'current_batch' => 'PM2025-001',
                'efficiency' => 92,
                'output_today' => 2450,
            ],
            [
                'name' => 'Yogurt Production Line',
                'status' => 'maintenance',
                'current_batch' => null,
                'efficiency' => 0,
                'output_today' => 0,
            ],
            [
                'name' => 'Cheese Processing Line',
                'status' => 'active',
                'current_batch' => 'CH2025-007',
                'efficiency' => 88,
                'output_today' => 340,
            ],
        ];

        return view('plant_manager.dashboard', compact(
            'user',
            'products',           // Added for the view
            'rawMaterials',       // Added for the view
            'totalLowStock',      // Added for the view
            'stats',              // Added for the view
            'productionStats',
            'inventoryStats',
            'recentOrders',
            'orderStats',
            'qualityAlerts',
            'productionLines'
        ));
    }

    private function getTodayProduction()
    {
        // This would typically come from production tracking system
        // For now, we'll simulate based on completed orders or inventory changes
        return Inventory::where('user_id', Auth::id())
                       ->whereDate('updated_at', today())
                       ->sum('quantity') ?? 3200;
    }
}
