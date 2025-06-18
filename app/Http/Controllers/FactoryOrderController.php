<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

class FactoryController extends Controller
{
    // View incoming orders from wholesalers
    public function index()
    {
        $incomingOrders = Order::where('seller_id', auth()->id())
                              ->with('buyer', 'items.product')
                              ->get();
        
        $suppliers = User::where('role', 'supplier')->get();
        $rawMaterials = Product::where('type', 'raw_material')->get();
        
        return view('factory.dashboard', compact('incomingOrders', 'suppliers', 'rawMaterials'));
    }

    // Approve wholesaler order
    public function approveOrder(Order $order)
    {
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'approved']);
        
        // Automatically create supplier order if needed
        $this->createSupplierOrder($order);

        return redirect()->route('factory.dashboard')
                        ->with('success', 'Wholesaler order approved and supplier order created!');
    }

    // Mark wholesaler order as shipped
    public function markShipped(Order $order)
    {
        if ($order->seller_id !== auth()->id() || $order->status !== 'approved') {
            abort(403);
        }

        $order->update(['status' => 'shipped']);

        return redirect()->route('factory.dashboard')
                        ->with('success', 'Order marked as shipped.');
    }

    // Create order to supplier
    protected function createSupplierOrder(Order $wholesalerOrder)
    {
        // Calculate required raw materials based on wholesaler order
        $requiredMaterials = $this->calculateRequiredMaterials($wholesalerOrder);
        
        // Find appropriate supplier
        $supplier = User::where('role', 'supplier')->first();
        
        $supplierOrder = Order::create([
            'buyer_id' => auth()->id(),
            'seller_id' => $supplier->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'parent_order_id' => $wholesalerOrder->id
        ]);
        
        // Add required materials to supplier order
        foreach ($requiredMaterials as $materialId => $quantity) {
            $supplierOrder->items()->create([
                'product_id' => $materialId,
                'quantity' => $quantity
            ]);
        }
        
        return $supplierOrder;
    }
    
    // Calculate raw materials needed for production
    protected function calculateRequiredMaterials(Order $order)
    {
        $requiredMaterials = [];
        
        foreach ($order->items as $item) {
            // Assuming each product has a bill of materials
            foreach ($item->product->materials as $material) {
                $requiredMaterials[$material->id] = isset($requiredMaterials[$material->id]) 
                    ? $requiredMaterials[$material->id] + ($material->pivot->quantity * $item->quantity)
                    : ($material->pivot->quantity * $item->quantity);
            }
        }
        
        return $requiredMaterials;
    }
    
    // View supplier orders
    public function supplierOrders()
    {
        $supplierOrders = Order::where('buyer_id', auth()->id())
                             ->whereHas('seller', function($q) {
                                 $q->where('role', 'supplier');
                             })
                             ->with('seller', 'items.product')
                             ->get();
        
        return view('factory.supplier_orders', compact('supplierOrders'));
    }
}
