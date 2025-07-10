<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class WholesalerDashboard extends Controller
{
    /**
     * Display the Wholesaler dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Orders
        $orders = \App\Models\Order::where('seller_id', $user->id)
            ->whereHas('buyer', function($q) {
                $q->where('role', 'retailer');
            })
            ->with(['items.product', 'buyer'])
            ->get();
        $orderStats = [
            'total_orders' => $orders->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'total_revenue' => $orders->where('status', 'completed')->sum(function($order) {
                return $order->items->sum(function($item) {
                    return $item->quantity * $item->price;
                });
            }),
        ];

        // Inventory
        $inventory = \App\Models\Inventory::where('user_id', $user->id)->with('product')->get();
        $inventoryStats = [
            'total_products' => $inventory->count(),
            'low_stock_items' => $inventory->where('quantity', '<=', 10)->count(),
            'out_of_stock' => $inventory->where('quantity', 0)->count(),
            'total_value' => $inventory->sum(function($inv) {
                return $inv->quantity * ($inv->product->price ?? 0);
            }),
        ];

        $lowStockItems = $inventory->where('quantity', '<=', 10);
        // Top Selling Products (by sales)
        $topProducts = $orders
            ->flatMap->items
            ->groupBy('product_id')
            ->map(function($items) {
                return [
                    'name' => $items->first()->product->name ?? 'Unknown',
                    'total_sold' => $items->sum('quantity'),
                    'total_revenue' => $items->sum(function($item) {
                        return $item->quantity * $item->unit_price;
                    }),
                ];
            })
            ->sortByDesc('total_sold')
            ->take(5)
            ->values();

        // Customer Insights
        $uniqueCustomers = $orders->groupBy('buyer_id');
        $totalCustomers = $uniqueCustomers->count();
        $repeatCustomers = $uniqueCustomers->filter(function($orders) {
            return $orders->count() > 1;
        })->count();
        $newCustomersThisMonth = $orders->where('created_at', '>=', now()->startOfMonth())->groupBy('buyer_id')->count();

        $customerStats = [
            'total_customers' => $totalCustomers,
            'repeat_customers' => $repeatCustomers,
            'new_customers_this_month' => $newCustomersThisMonth,
        ];
        $recentOrders = $orders->sortByDesc('created_at')->take(5);
        $incomingOrders = $orders->sortByDesc('created_at')->take(5);
        $outgoingOrders = \App\Models\Order::where('buyer_id', $user->id)
            ->with(['items.product', 'seller'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Monthly Revenue for the current year
        $monthlyRevenue = \App\Models\Order::where('seller_id', $user->id)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        return view('wholesaler.dashboard', compact(
            'orderStats',
            'inventoryStats',
            'lowStockItems',
            'topProducts',
            'monthlyRevenue',
            'customerStats',
            'incomingOrders',
            'outgoingOrders',
            'orders'
        ));
    }
}
