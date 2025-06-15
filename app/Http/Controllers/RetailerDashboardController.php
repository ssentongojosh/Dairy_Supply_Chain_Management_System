<?php

namespace App\Http\Controllers\Retailer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RetailerDashboardController extends Controller
{
    public function index()
    {
        $stockLevels = [
            'Milk' => 120,
            'Cheese' => 40,
            'Butter' => 20,
        ];

        $activeOrders = 5;
        $lowStockAlerts = ['Butter'];

        $recentOrders = [
            ['id' => 1, 'product' => 'Cheese', 'quantity' => 10],
            ['id' => 2, 'product' => 'Milk', 'quantity' => 30],
        ];

        return view('retailer.dashboard', compact('stockLevels', 'activeOrders', 'lowStockAlerts', 'recentOrders'));
    }
}