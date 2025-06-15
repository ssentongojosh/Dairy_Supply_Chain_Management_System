<?php

namespace App\Http\Controllers\Wholesaler;

use App\Http\Controllers\Controller;

class WholesalerDashboardController extends Controller
{
    public function index()
    {
        $inventory = [
            'Milk Crates' => 300,
            'Cheese Blocks' => 120,
            'Butter Packs' => 80,
        ];

        $orders = [
            ['id' => 101, 'retailer' => 'Retailer A', 'product' => 'Cheese Blocks', 'quantity' => 20],
            ['id' => 102, 'retailer' => 'Retailer B', 'product' => 'Milk Crates', 'quantity' => 50],
        ];

        $lowStock = ['Butter Packs'];

        return view('wholesaler.dashboard', compact('inventory', 'orders', 'lowStock'));
    }
}

