<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RawMaterialOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupplierOrderController extends Controller
{
    public function index()
    {
        // Incoming regular orders from factories
        $incomingOrders = Order::where('seller_id', Auth::id())->with('buyer', 'items.product')->get();

        // Incoming raw material orders from plant managers
        $incomingRawMaterialOrders = RawMaterialOrder::where('seller_id', Auth::id())
            ->with('buyer', 'items.rawMaterial')
            ->get();

        return view('supplier.dashboard', compact('incomingOrders', 'incomingRawMaterialOrders'));
    }

    public function orderHistory()
    {
        // Get both regular orders and raw material orders
        $regularOrders = Order::where('seller_id', Auth::id())
            ->with('buyer', 'items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        $rawMaterialOrders = RawMaterialOrder::where('seller_id', Auth::id())
            ->with('buyer', 'items.rawMaterial')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('supplier.orders.history', compact('regularOrders', 'rawMaterialOrders'));
    }

    public function showOrder(Request $request, $orderId)
    {
        // First try to find it as a regular order
        $order = Order::find($orderId);

        Log::info('showOrder supplier called', [
            'orderId' => $orderId,
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role
        ]);

        // if ($order) {
        //     if ($order->user_id !== Auth::id()) {
        //         abort(403);
        //     }

            $order->load('buyer', 'items.rawMaterial');
            return view('supplier.orders.show', compact('order'));

            // If not found as regular order, try to find it as a raw material order
            // $rawMaterialOrder = RawMaterialOrder::find($orderId);

            if ($rawMaterialOrder) {
                // Use the existing showRawMaterialOrder method
                return $this->showRawMaterialOrder($rawMaterialOrder);
            } else {
                abort(404, 'Order not found.');
            }
        }


    public function showRawMaterialOrder(RawMaterialOrder $order)
    {
        // if ($rawMaterialOrder->seller_id !== Auth::id()) {
        //     abort(403);
        // }

        $order->load('buyer', 'items.rawMaterial');
        return view('supplier.orders.show-raw-material', compact('order'));
    }

 public function approveOrder(Order $order)
{
    if ($order->seller_id !== Auth::id()) {
        abort(403);
    }

    // Only approve if payment is completed
    if ($order->payment_status === 'paid') {
        $order->update(['status' => 'approved']);
        return redirect()->route('supplier.orders')->with('success', 'Order approved!');
    }

    return back()->with('error', 'Order must be paid before approval');
}

public function approveRawMaterialOrder(RawMaterialOrder $rawMaterialOrder)
{
    if ($rawMaterialOrder->seller_id !== Auth::id()) {
        abort(403);
    }

    $rawMaterialOrder->update(['status' => 'approved']);
    return redirect()->route('supplier.orders')->with('success', 'Raw material order approved!');
}

public function rejectOrder(Order $order)
{
    if ($order->seller_id !== Auth::id()) {
        abort(403);
    }

    $order->update(['status' => 'rejected']);
    return redirect()->route('supplier.orders')->with('success', 'Order rejected.');
}

public function rejectRawMaterialOrder(RawMaterialOrder $rawMaterialOrder)
{
    if ($rawMaterialOrder->seller_id !== Auth::id()) {
        abort(403);
    }

    $rawMaterialOrder->update(['status' => 'rejected']);
    return redirect()->route('supplier.orders')->with('success', 'Raw material order rejected.');
}

public function markShipped(Order $order)
{
    if ($order->seller_id !== Auth::id() || $order->status !== 'approved') {
        abort(403);
    }

    // Only ship if payment is completed
    if ($order->payment_status === 'paid') {
        $order->update(['status' => 'shipped']);
        return redirect()->route('supplier.orders')->with('success', 'Order marked as shipped.');
    }

    return back()->with('error', 'Order must be paid before shipping');
}

public function markRawMaterialOrderShipped(RawMaterialOrder $rawMaterialOrder)
{
    if ($rawMaterialOrder->seller_id !== Auth::id() || $rawMaterialOrder->status !== 'approved') {
        abort(403);
    }

    $rawMaterialOrder->update(['status' => 'shipped']);
    
    return redirect()->route('supplier.orders')->with('success', 'Raw material order marked as shipped.');
}
}

