<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        // Incoming orders from factories
        $incomingOrders = Order::where('seller_id', auth()->id())->with('buyer', 'items.product')->get();

        return view('supplier.dashboard', compact('incomingOrders'));
    }

    public function approveOrder(Order $order)
    {
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'approved']);

        return redirect()->route('supplier.dashboard')->with('success', 'Order approved!');
    }

    public function markShipped(Order $order)
    {
        if ($order->seller_id !== auth()->id() || $order->status !== 'approved') {
            abort(403);
        }

        $order->update(['status' => 'shipped']);

        return redirect()->route('supplier.dashboard')->with('success', 'Order marked as shipped.');
    }
}

