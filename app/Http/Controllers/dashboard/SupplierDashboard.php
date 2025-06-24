<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierDashboard extends Controller
{
    /**
     * Show the supplier dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Order Statistics
        $orderStats = [
            'total_orders' => Order::where('seller_id', $user->id)->count(),
            'pending_orders' => Order::where('seller_id', $user->id)
                                   ->where('status', 'pending')
                                   ->count(),
            'processing_orders' => Order::where('seller_id', $user->id)
                                       ->whereIn('status', ['approved', 'processing'])
                                       ->count(),
            'completed_orders' => Order::where('seller_id', $user->id)
                                     ->whereIn('status', ['shipped', 'delivered'])
                                     ->count(),
            'total_revenue' => Order::where('seller_id', $user->id)
                                  ->whereIn('status', ['shipped', 'delivered'])
                                  ->sum('total_amount'),
        ];

        // Inventory Statistics
        $inventoryStats = [
            'total_products' => Inventory::where('user_id', $user->id)->count(),
            'low_stock_items' => Inventory::where('user_id', $user->id)
                                        ->whereRaw('quantity <= threshold')
                                        ->count(),
            'out_of_stock' => Inventory::where('user_id', $user->id)
                                     ->where('quantity', 0)
                                     ->count(),
            'total_value' => Inventory::where('user_id', $user->id)
                                   ->sum(DB::raw('quantity * unit_price')),
        ];

        // Customer Statistics
        $customerStats = [
            'total_customers' => Order::where('seller_id', $user->id)
                                   ->distinct('buyer_id')
                                   ->count('buyer_id'),
            'repeat_customers' => Order::where('seller_id', $user->id)
                                    ->select('buyer_id')
                                    ->groupBy('buyer_id')
                                    ->havingRaw('COUNT(*) > 1')
                                    ->count(),
            'new_customers_this_month' => Order::where('seller_id', $user->id)
                                            ->whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)
                                            ->distinct('buyer_id')
                                            ->count('buyer_id'),
        ];

        // Recent Orders
        $recentOrders = Order::where('seller_id', $user->id)
                           ->with(['buyer', 'items.product'])
                           ->orderBy('created_at', 'desc')
                           ->limit(5)
                           ->get();

        // Low Stock Items
        $lowStockItems = Inventory::where('user_id', $user->id)
                                ->with('product')
                                ->whereRaw('quantity <= threshold')
                                ->orderBy('quantity', 'asc')
                                ->limit(5)
                                ->get();

        // Top Selling Products (this month)
        $topProducts = DB::table('order_items')
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->join('products', 'order_items.product_id', '=', 'products.id')
                        ->where('orders.seller_id', $user->id)
                        ->whereMonth('orders.created_at', now()->month)
                        ->whereYear('orders.created_at', now()->year)
                        ->select('products.name', 
                               DB::raw('SUM(order_items.quantity) as total_sold'),
                               DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue'))
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('total_sold', 'desc')
                        ->limit(5)
                        ->get();

        // Monthly Revenue Data (last 6 months)
        $monthlyRevenue = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Order::where('seller_id', $user->id)
                          ->whereMonth('created_at', $date->month)
                          ->whereYear('created_at', $date->year)
                          ->whereIn('status', ['shipped', 'delivered'])
                          ->sum('total_amount');
            
            $monthlyRevenue->push([
                'month' => $date->format('M Y'),
                'revenue' => $revenue
            ]);
        }

        // Recent Customer Activities
        $recentCustomers = User::whereIn('id', function($query) use ($user) {
                               $query->select('buyer_id')
                                    ->from('orders')
                                    ->where('seller_id', $user->id)
                                    ->whereDate('created_at', '>=', now()->subDays(30));
                           })
                           ->with(['orders' => function($query) use ($user) {
                               $query->where('seller_id', $user->id)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(1);
                           }])
                           ->limit(5)
                           ->get();

        return view('supplier.dashboard', compact(
            'orderStats',
            'inventoryStats',
            'customerStats',
            'recentOrders',
            'lowStockItems',
            'topProducts',
            'monthlyRevenue',
            'recentCustomers'
        ));
    }
}
