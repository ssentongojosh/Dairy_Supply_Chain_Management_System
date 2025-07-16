@extends('layouts.contentNavbarLayout')

@section('content')
<h2 class="text-xl font-bold mb-4">Order History</h2>

@php
    $canPlaceOrders = in_array(is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role, ['retailer', 'wholesaler', 'plant_manager']);
@endphp

@if($canPlaceOrders)
    <!-- Placed Orders -->
    <h3 class="text-lg font-semibold mt-6 mb-2">Your Orders (Placed)</h3>
    @forelse($placedOrders as $order)
        <div class="border p-4 rounded mb-3 bg-gray-100">
            <p><strong>To:</strong> {{ $order->seller->name }} ({{ $order->seller->role }})</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Total:</strong> {{ $order->total_price }} UGX</p>
            <ul class="mt-2">
                @foreach($order->items as $item)
                    <li>📦 {{ $item->product->name }} — Qty: {{ $item->quantity }}</li>
                @endforeach
            </ul>
        </div>
    @empty
        <p class="text-gray-500">No placed orders yet.</p>
    @endforelse
@endif

<!-- Received Orders -->
<h3 class="text-lg font-semibold mt-6 mb-2">Orders You Received</h3>
@php
    $tableRoles = ['farmer', 'supplier'];
    $userRole = is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role;
    $showRoute = $userRole === 'farmer' ? 'farmer.orders.show' : ($userRole === 'supplier' ? 'supplier.orders.show' : 'orders.show');
@endphp
@if(in_array($userRole, $tableRoles))
    <!-- Filter/Search Bar -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>From</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Items</th>
                    <th>Total (UGX)</th>
                    <th>Placed On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receivedOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->buyer->name }} ({{ $order->buyer->role }})</td>
                        <td>
                            @php
                                $statusClass = match($order->status) {
                                    'pending' => 'bg-label-warning',
                                    'approved' => 'bg-label-primary',
                                    'processing' => 'bg-label-info',
                                    'shipped' => 'bg-label-success',
                                    'delivered' => 'bg-label-success',
                                    'rejected' => 'bg-label-danger',
                                    'cancelled' => 'bg-label-secondary',
                                    default => 'bg-label-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td>
                            @php
                                $paymentClass = match($order->payment_status ?? 'pending') {
                                    'paid' => 'bg-label-success',
                                    'pending' => 'bg-label-warning',
                                    'failed' => 'bg-label-danger',
                                    'refunded' => 'bg-label-info',
                                    default => 'bg-label-secondary'
                                };
                            @endphp
                            <span class="badge {{ $paymentClass }}">
                                {{ ucfirst($order->payment_status ?? 'pending') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-label-info">{{ $order->items->count() }} items</span>
                            <div class="mt-1">
                                @foreach($order->items->take(2) as $item)
                                    <small class="text-muted d-block">{{ $item->product->name ?? 'Unknown Product' }}</small>
                                @endforeach
                                @if($order->items->count() > 2)
                                    <small class="text-muted">+{{ $order->items->count() - 2 }} more</small>
                                @endif
                            </div>
                        </td>
                        <td>{{ $order->total_price ?? $order->total_amount }} UGX</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route($showRoute, $order) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No received orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@else
    @forelse($receivedOrders as $order)
        <div class="border p-4 rounded mb-3 bg-green-50">
            <p><strong>From:</strong> {{ $order->buyer->name }} ({{ $order->buyer->role }})</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Total:</strong> {{ $order->total_price }} UGX</p>
            <ul class="mt-2">
                @foreach($order->items as $item)
                    <li>📦 {{ $item->product->name }} — Qty: {{ $item->quantity }}</li>
                @endforeach
            </ul>
        </div>
    @empty
        <p class="text-gray-500">No received orders yet.</p>
    @endforelse
@endif
@if($canPlaceOrders && isset($order) && !$order->payment && $order->status === 'pending')
    <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="mt-2">
        @csrf
        <button type="submit" class="text-red-600 hover:underline">
            ❌ Cancel Order
        </button>
    </form>
@endif
@endsection