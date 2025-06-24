<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WholesalerDashboard extends Controller
{
    /**
     * Display the wholesaler dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Dashboard metrics (mock data)
        $pendingOrdersCount      = 5;
        $lowStockProductsCount   = 2;
        $totalSalesThisMonth     = 1250000.00;
        $newOrdersToday          = 7;
        $totalUniqueProducts     = 50;
        $outOfStockProductsCount = 1;

        // Sample chart data
        $salesChartLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $salesChartData   = [300000, 350000, 320000, 280000];

        $ordersChartLabels = $salesChartLabels;
        $ordersChartData   = [20, 25, 22, 18];

        // Mock recent orders
        $recentOrders = collect([
            (object) ['id' => 2001, 'customer_name' => 'ACME Retail', 'total_amount' => 500000, 'status' => 'pending', 'created_at' => Carbon::now()->subDays(1)],
            (object) ['id' => 2002, 'customer_name' => 'BestStores Ltd', 'total_amount' => 750000, 'status' => 'shipped', 'created_at' => Carbon::now()->subDays(2)],
            (object) ['id' => 2003, 'customer_name' => 'LocalMart', 'total_amount' => 300000, 'status' => 'delivered', 'created_at' => Carbon::now()->subDays(3)],
        ]);

        // Mock products to reorder
        $productsToReorder = collect([
            (object)[ 'name' => 'Yogurt 500g',    'sku' => 'YOGT500', 'current_stock' => 5,  'reorder_point' => 15 ],
            (object)[ 'name' => 'Cheese Block',    'sku' => 'CHED300', 'current_stock' => 2,  'reorder_point' => 10 ],
            (object)[ 'name' => 'Butter Pack',     'sku' => 'BTR100',  'current_stock' => 0,  'reorder_point' => 8  ],
        ]);

        // Mock key suppliers
        $keySuppliers = collect([
            (object)[ 'name' => 'Dairy Source Inc', 'contact_person' => 'Eve Adams', 'total_orders_this_month' => 6 ],
            (object)[ 'name' => 'FarmFresh Foods',  'contact_person' => 'Tom Brown', 'total_orders_this_month' => 9 ],
            (object)[ 'name' => 'Creamy Delights',  'contact_person' => 'Sara Lee',  'total_orders_this_month' => 4 ],
        ]);

        return view('dashboard.wholesaler', [
            'user'                    => $user,
            'pendingOrdersCount'      => $pendingOrdersCount,
            'lowStockProductsCount'   => $lowStockProductsCount,
            'totalSalesThisMonth'     => $totalSalesThisMonth,
            'newOrdersToday'          => $newOrdersToday,
            'totalUniqueProducts'     => $totalUniqueProducts,
            'outOfStockProductsCount' => $outOfStockProductsCount,
            'salesChartLabels'        => $salesChartLabels,
            'salesChartData'          => $salesChartData,
            'ordersChartLabels'       => $ordersChartLabels,
            'ordersChartData'         => $ordersChartData,
            'recentOrders'            => $recentOrders,
            'productsToReorder'       => $productsToReorder,
            'keySuppliers'            => $keySuppliers,
        ]);
    }
}
