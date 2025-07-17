@extends('layouts.contentNavbarLayout')

@section('content')

<!-- page heading -->
<h1 class="text-center mb-4">Deliveries</h1>

<!-- trying ut the blade for raw materials -->
 <a href = "{{ route('inventory.raw_materials') }}">raw materials</a>

<!-- table heading -->
<div class="card-header border-primary d-flex justify-content-between align-items-center mb-4 py-2">
    <h3 class="text-black">
        <i class="ri-file-download-line me-2 fs-3"></i> INCOMING
    </h3>

    <!-- back button -->
    <a href="{{ route('plant_manager.dashboard') }}" class="btn btn-outline-primary">
        Back
    </a>
</div>


<!-- table for incoming -->

 <div class="card body">
 <table class="table table-striped table-bordered">
    <!-- table widths-->
    <colgroup>
        <col style="width: 20%;">  <!-- Item Name -->
        <col style="width: 10%;">   <!-- Quantity -->
        <col style="width: 10%;">  <!-- Order ID -->
        <col style="width: 10%;">  <!-- Sender -->
        <col style="width: 20%;">  <!-- Status -->
        <col style="width: 20%;">  <!-- Delivery Date -->
        <!-- <col style="width: 25%;">
        <col style="width: 10%;"> -->
    </colgroup>
    <thead>
        <tr>
            <th>Name:</th>
            <th>Quantity:</th>
            <th>Order:</th>
            <th>Sender:</th>
            <th>Status:</th>
            <th>Date:</th>
            <!-- <th>Notes:</th>
            <th>Action:</th> -->
        </tr>
    </thead>
    <tbody>
        @foreach($delivery as $item)
        <tr>
            <td>{{ $item->item_name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->order_id }}</td>
            <td>{{ $item->sender_id }}</td>
            <td>
                <form method="POST" action="{{ route('delivery.updateStatus', $item->id) }}">
                   @csrf
                   @method('PUT')

                    <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded px-2 py-1
                       @switch($item->status)
                       @case('pending') bg-yellow-100 text-yellow-800 @break
                       @case('transit') bg-blue-100 text-blue-800 @break
                       @case('delivered') bg-green-100 text-green-800 @break
                       @case('rejected') bg-red-100 text-red-800 @break
                       @default bg-gray-100 text-gray-800
                       @endswitch
                       ">
                       <option value="pending"   @selected($item->status == 'pending')>Pending</option>
                       <option value="transit"   @selected($item->status == 'transit')>In Transit</option>
                       <option value="delivered" @selected($item->status == 'delivered')>Delivered</option>
                       <option value="rejected"  @selected($item->status == 'rejected')>Rejected</option>
                    </select>
                </form>
            </td>
            <td>{{ \Carbon\Carbon::parse($item->delivery_date)->format('Y-m-d') }}</td>
            <!-- <td>{{ $item->notes ?? 'No notes' }}</td>
            <td>
                <form method="POST" action="{{ route('delivery.confirm', $item->id) }}">
                   @csrf
                   @method('PUT')
                   <button name="action" value="confirm">✅</button>
                   <button name="action" value="reject">❌</button>
                </form>
            </td> -->
        </tr>
        @endforeach

    </tbody>
 </table>
 <!-- trying to see status -->
  <!-- @foreach($delivery as $item)
 <a href="{{ route('delivery.statusPage', ['id' => $item->id]) }}" class="btn btn-info">
    View Delivery Status
</a>
@endforeach -->

 </div>

 @endsection