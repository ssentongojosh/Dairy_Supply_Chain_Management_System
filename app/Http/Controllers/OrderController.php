<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Inventory;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected OrderWorkflowService $workflow;

    public function __construct(OrderWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * View all incoming orders for authenticated user based on role
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role->value; // assuming enum Role

        $orders = Order::where('seller_id', $user->id)
            ->when($role === 'wholesaler', fn($q) => $q->whereHas('buyer', fn($q) => $q->where('role', 'retailer')))
            ->when($role === 'factory', fn($q) => $q->whereHas('buyer', fn($q) => $q->where('role', 'wholesaler')))
            ->when($role === 'supplier', fn($q) => $q->whereHas('buyer', fn($q) => $q->where('role', 'factory')))
            ->when($role === 'farmer', fn($q) => $q->whereHas('items.product', fn($q) => $q->where('category', 'milk')))
            ->with(['buyer', 'items.product'])
            ->get();

        return view("{$role}.dashboard", compact('orders'));
    }

    /**
     * Store new order from buyer to seller
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'buyer_id' => Auth::id(),
                'seller_id' => $validated['seller_id'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

<<<<<<< HEAD
            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => 0,
                ]);
            }

            $this->workflow->processNewOrder($order);
            DB::commit();
=======
            case 'factory':
                // TODO: Create factory order controller
                return redirect()->route('dashboard')->with('info', 'Factory order management coming soon!');
>>>>>>> parent of 1505f91 (Added cards for creating supplier and farmer orders)

<<<<<<< HEAD
            return back()->with('success', 'Order placed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to place order.');
=======
            case 'supplier':
                // Redirect suppliers to their order dashboard
                return redirect()->route('supplier.orders');

            case 'admin':
                // TODO: Create admin order overview
                return redirect()->route('dashboard')->with('info', 'Admin order overview coming soon!');

            default:
                return redirect()->route('dashboard')->with('error', 'Access denied.');
>>>>>>> parent of c8838a8 (Add order history and order details pages for suppliers)
        }
    }

    /**
     * Update order status by seller
     */
    public function updateStatus(Order $order, Request $request)
    {
        $user = Auth::user();

        if ($order->seller_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:approved,shipped,ready_for_delivery,delivered,received,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        if ($validated['status'] === 'received' && $user->role->value === 'retailer') {
            foreach ($order->items as $item) {
                $inventory = Inventory::firstOrNew([
                    'user_id' => $user->id,
                    'product_id' => $item->product_id,
                ]);

                $inventory->quantity += $item->quantity;
                $inventory->unit_cost = $item->unit_price;
                $inventory->selling_price = $item->unit_price * 1.2;
                $inventory->reorder_point = $inventory->reorder_point ?? 10;
                $inventory->save();
            }
        }

        return back()->with('success', 'Order status updated.');
    }

    /**
     * Show payment form
     */
    public function showPayment(Order $order)
    {
        $user = Auth::user();

        if ($order->buyer_id !== $user->id || !$order->requiresPayment()) {
            abort(403);
        }

        return view('payments.initiate', compact('order'));
    }

    /**
     * Process payment
     */
    public function processPayment(Order $order, Request $request)
    {
        $user = Auth::user();

        if ($order->buyer_id !== $user->id || !$order->requiresPayment()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $payment = $this->workflow->processPayment($order, [
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

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order)
    {
        $user = Auth::user();

        if ($order->buyer_id !== $user->id || !in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'Cannot cancel this order.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Order cancelled.');
    }

    /**
     * View all orders placed by the user (as buyer)
     */
    public function outgoingOrders()
    {
        $user = Auth::user();

        $orders = Order::where('buyer_id', $user->id)
            ->with(['seller', 'items.product'])
            ->orderByDesc('created_at')
            ->get();

        return view($user->role->value . '.orders', compact('orders'));
    }
}
