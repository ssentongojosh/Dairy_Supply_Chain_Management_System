<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

class RetailerController extends Controller
{
    // Retailer dashboard showing products and orders
    public function index()
    {
        $wholesalers = User::where('role', 'wholesaler')->with('products')->get();
        $outgoingOrders = Order::where('buyer_id', auth()->id())
                             ->with('seller', 'items.product')
                             ->get();
        
        return view('retailer.dashboard', compact('wholesalers', 'outgoingOrders'));
    }

    // Create new order to wholesaler
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'wholesaler_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = Order::create([
            'buyer_id' => auth()->id(),
            'seller_id' => $validated['wholesaler_id'],
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('retailer.dashboard')
                        ->with('success', 'Order placed successfully!');
    }

    // View order details
    public function showOrder(Order $order)
    {
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        return view('retailer.order_show', compact('order'));
    }

    // Mark order as received
    public function markReceived(Order $order)
    {
        if ($order->buyer_id !== auth()->id() || $order->status !== 'shipped') {
            abort(403);
        }

        $order->update(['status' => 'received']);

        // Update inventory
        foreach ($order->items as $item) {
            auth()->user()->inventory()->updateOrCreate(
                ['product_id' => $item->product_id],
                ['quantity' => DB::raw("quantity + {$item->quantity}")]
            );
        }

        return redirect()->route('retailer.dashboard')
                        ->with('success', 'Order marked as received and inventory updated!');
    }
}
