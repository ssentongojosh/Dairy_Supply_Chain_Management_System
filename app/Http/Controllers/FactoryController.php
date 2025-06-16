<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class FactoryController extends Controller
{
    public function index()
    {
        // Incoming orders from wholesalers
        $incomingOrders = Order::where('seller_id', auth()->id())->with('buyer', 'items')->get();

        return view('factory.dashboard', compact('incomingOrders'));
    }

    public function approveOrder(Order $order)
    {
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'approved']);

        return redirect()->route('factory.dashboard')->with('success', 'Order approved!');
    }

    public function createOrder()
    {
        // Get suppliers and their products
        $suppliers = User::role('supplier')->get();
        $products = Product::whereIn('owner_id', $suppliers->pluck('id'))->get();

        return view('factory.create-order', compact('suppliers', 'products'));
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $order = Order::create([
                'buyer_id' => auth()->id(),
                'seller_id' => $request->seller_id,
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            $order->update(['total_amount' => $total]);
        });

        return redirect()->route('factory.dashboard')->with('success', 'Order sent to supplier.');
    }
}

