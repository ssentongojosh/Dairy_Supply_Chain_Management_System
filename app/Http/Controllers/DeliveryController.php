<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialBatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //to the blade
        $delivery = Delivery::orderBy('created_at', 'desc')->get();
        return view('delivery.index', compact('delivery'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //delivery form
        $users = User::all();
        return view('delivery.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //what to store in the database
        $delivery = Delivery::create([
            'item_name' => $request->item_name,
            'quantity' => $request->quantity,
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'order_id' => $request->order_id,
            'delivery_date' => now(),
            'status' => 'pending',
            'notes' => $request->notes,
            'confirmed' => false,
            'from' => 'supplier',
            'to' => 'plant',
        ]);

        if ($request->filled('order_id')) {
            $deliveryData['order_id'] = $request->order_id;
        }

        //return for the status page 
        return redirect()->route('delivery.statusPage', $delivery->id);

    }

    //to confirm by the manager once things arrive
    public function confirm(Request $request, $id)
{
    $delivery = Delivery::findOrFail($id);

    if ($request->action === 'confirm') {
        $delivery->status = 'delivered';
        $delivery->confirmed = true;
    } elseif ($request->action === 'reject') {
        $delivery->status = 'rejected';
        $delivery->confirmed = false;
    }

    $delivery->save();

    return back()->with('success', 'Delivery updated');
}

    //update the status 
    public function updateStatus(Request $request, $id)
{
    $delivery = Delivery::findOrFail($id);
    $delivery->status = $request->input('status');
    $delivery->save();

    return back()->with('success', 'Delivery status updated.');
}
 
     //for continous check of the delivery id waiting for confirmation
     public function checkStatus($id)
    {
       $delivery = Delivery::findOrFail($id);
       return response()->json(['status' => $delivery->status]);
    }

    //status page
    public function statusPage($id)
{
    $delivery = Delivery::findOrFail($id);
    //track supplier
    $delivery = Delivery::with('sender')->find($id);
    //gpt
    if (!$delivery) {
        abort(404, 'Delivery not found');
    }
    return view('delivery.status', compact('delivery'));
}
    //terminate delivery
    public function terminate($id)
{
    $delivery = Delivery::findOrFail($id);

    if ($delivery->status === 'pending') {
        $delivery->status = 'terminated';
        $delivery->save();
    }

    return redirect()->route('delivery.create')->with('message', 'Delivery was terminated.');
}

    //for my deliveries
    
public function myDeliveries()
{
    // Only show deliveries by the logged-in user
    $delivery = Delivery::where('sender_id', Auth::id())->latest()->get();

    return view('delivery.mine', compact('delivery'));
}
     
    //when deliveries reach to enter rawMaterialBatch
public function confirmDelivery(Request $request, $deliveryId)
{
    $delivery = RawMaterialDelivery::findOrFail($deliveryId);

    if ($delivery->status !== 'confirmed') {
        RawMaterialBatch::create([
            'raw_material_id' => $delivery->raw_material_id,
            'quantity' => $delivery->quantity,
            'delivery_date' => now(),
            'supplier_id' => $delivery->supplier_id
        ]);

        // Update total stock
        $material = RawMaterial::find($delivery->raw_material_id);
        $material->quantity += $delivery->quantity;
        $material->save();

        $delivery->status = 'confirmed';
        $delivery->save();
    }

    return redirect()->back()->with('success', 'Delivery confirmed and stock updated.');
}

    //trying out the fill in order then the rest enters
    public function autoCreate(Request $request)
{
    $orderId = $request->input('order_id');
    $order = Order::with('items')->findOrFail($orderId);

    foreach ($order->items as $item) {
        Delivery::create([
            'order_id'      => $order->id,
            'raw_material_id'    => $item->raw_material_id,
            'quantity'      => $item->quantity,
            'recipient'     => $order->recipient_name ?? 'Plant Manager', // adjust based on your schema
            'location'      => $order->delivery_location ?? 'Main Plant',
            'delivery_date' => now(),
            'status'        => 'pending',
        ]);
    }

    return back()->with('success', 'Delivery created automatically from order.');
}



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
