<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Inventory;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RetailerOrderController extends Controller
{
    // Retailer dashboard showing products and orders
    public function index()
    {
        $wholesalers = User::where('role', 'wholesaler')->with('products')->get();
        $outgoingOrders = Order::where('buyer_id', Auth::id())
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

        try {
            DB::beginTransaction();

            $order = Order::create([
                'buyer_id' => Auth::id(),
                'seller_id' => $validated['wholesaler_id'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => 0, // Will be calculated by workflow service
                ]);
            }

            // Process the order through workflow service
            $workflowService = new OrderWorkflowService();
            $workflowService->processNewOrder($order);

            DB::commit();

            $message = $order->status === 'approved'
                ? 'Order placed and approved! You can now proceed with payment.'
                : 'Order placed successfully! Awaiting approval.';

            return redirect()->route('retailer.orders')
                            ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                            ->with('error', 'Failed to place order. Please try again.');
        }
    }

    // View order details
    public function showOrder(Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'seller', 'latestPayment']);
        return view('retailer.order_show', compact('order'));
    }

    // Show payment form for approved order
    public function showPaymentForm(Order $order)
    {
        if ($order->buyer_id !== Auth::id() || !$order->requiresPayment()) {
            abort(403);
        }

        return view('payments.initiate', compact('order'));
    }

    // Process payment
    public function processPayment(Order $order, Request $request)
    {
        if ($order->buyer_id !== Auth::id() || !$order->requiresPayment()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $workflowService = new OrderWorkflowService();
        $payment = $workflowService->processPayment($order, [
            'method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
        ]);

        if ($payment) {
            return redirect()->route('retailer.orders.show', $order)
                            ->with('success', 'Payment submitted! Awaiting verification.');
        }

        return redirect()->back()
                        ->with('error', 'Payment processing failed. Please try again.');
    }

    // Mark order as received
    public function markReceived(Order $order)
    {
        if ($order->buyer_id !== Auth::id() || $order->status !== 'shipped') {
            abort(403);
        }

        $order->update(['status' => 'received']);

        // Update inventory
        foreach ($order->items as $item) {
            $inventory = Inventory::where('user_id', Auth::id())
                ->where('product_id', $item->product_id)
                ->first();

            if ($inventory) {
                $inventory->increment('quantity', $item->quantity);
            } else {
                Inventory::create([
                    'user_id' => Auth::id(),
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'reorder_point' => 10, // Default reorder point
                    'unit_cost' => $item->unit_price,
                    'selling_price' => $item->unit_price * 1.2, // 20% markup
                ]);
            }
        }

        return redirect()->route('retailer.dashboard')
                        ->with('success', 'Order marked as received and inventory updated!');
    }

    // Show order history page
    public function orderHistory(Request $request)
    {

        $query = Order::where('buyer_id', Auth::id())
                     ->with(['seller', 'items.product']);

        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->whereHas('seller', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate total amount for each order
        $orders->transform(function ($order) {
            $order->total_amount = $order->items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            return $order;
        });


        return view('retailer.order_history', compact('orders' ));
    }

    // Cancel order
    public function cancelOrder(Order $order)
    {
        if ($order->buyer_id !== Auth::id() || !in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['success' => false, 'message' => 'Cannot cancel this order'], 403);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully']);
    }
}
