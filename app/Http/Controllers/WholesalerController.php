<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

class WholesalerController extends Controller
{
    // View incoming orders from retailers
    public function index()
    {
        $incomingOrders = Order::where('seller_id', auth()->id())
                              ->with('buyer', 'items.product')
                              ->get();
        
        $factories = User::where('role', 'factory')->get();
        $products = Product::all();
        
        return view('wholesaler.dashboard', compact('incomingOrders', 'factories', 'products'));
    }

    // Approve retailer order
    public function approveOrder(Order $order)
    {
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'approved']);
        
        // Automatically create factory order if needed
        $this->createFactoryOrder($order);

        return redirect()->route('wholesaler.dashboard')
                        ->with('success', 'Retailer order approved and factory order created!');
    }

    // Mark retailer order as shipped
    public function markShipped(Order $order)
    {
        if ($order->seller_id !== auth()->id() || $order->status !== 'approved') {
            abort(403);
        }

        $order->update(['status' => 'shipped']);

        return redirect()->route('wholesaler.dashboard')
                        ->with('success', 'Order marked as shipped.');
    }

    // Create order to factory
    public function createFactoryOrder(Order $retailerOrder)
    {
        // Find default factory or use logic to select appropriate factory
        $factory = User::where('role', 'factory')->first();
        
        $factoryOrder = Order::create([
            'buyer_id' => auth()->id(),
            'seller_id' => $factory->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'parent_order_id' => $retailerOrder->id // Link to retailer order
        ]);
        
        // Convert retailer order items to factory order items
        foreach ($retailerOrder->items as $item) {
            $factoryOrder->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity * 2 // Example: order 2x from factory for 1x retailer order
            ]);
        }
        
        return $factoryOrder;
    }
    
    // View factory orders
    public function factoryOrders()
    {
        $factoryOrders = Order::where('buyer_id', auth()->id())
                             ->whereHas('seller', function($q) {
                                 $q->where('role', 'factory');
                             })
                             ->with('seller', 'items.product')
                             ->get();
        
        return view('wholesaler.factory_orders', compact('factoryOrders'));
    }
}

