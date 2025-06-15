<?php

namespace App\Http\Controllers\Factory;

use App\Http\Controllers\Controller;

class FactoryDashboardController extends Controller
{
    public function index()
    {
        $production = [
            'Processed Milk Units' => 1000,
            'Cheese Wheels' => 450,
            'Butter Bars' => 300,
        ];

        $shipments = [
            ['id' => 301, 'to' => 'Supplier A', 'product' => 'Cheese Wheels', 'quantity' => 150],
            ['id' => 302, 'to' => 'Supplier B', 'product' => 'Butter Bars', 'quantity' => 100],
        ];

        $delays = ['Cheese Wheels']; // e.g. due to machinery or raw material

        return view('factory.dashboard', compact('production', 'shipments', 'delays'));
    }
}
