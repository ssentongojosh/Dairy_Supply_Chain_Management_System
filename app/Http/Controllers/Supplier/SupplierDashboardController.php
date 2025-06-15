<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;

class SupplierDashboardController extends Controller
{
    public function index()
    {
        $stock = [
            'Flour Bags' => 500,
            'Sugar Packs' => 350,
            'Yeast Packs' => 100,
        ];

        $deliveries = [
            ['id' => 201, 'wholesaler' => 'Wholesaler X', 'product' => 'Flour Bags', 'quantity' => 100],
            ['id' => 202, 'wholesaler' => 'Wholesaler Y', 'product' => 'Sugar Packs', 'quantity' => 50],
        ];

        $lowStockAlerts = ['Yeast Packs'];

        return view('supplier.dashboard', compact('stock', 'deliveries', 'lowStockAlerts'));
    }
}

