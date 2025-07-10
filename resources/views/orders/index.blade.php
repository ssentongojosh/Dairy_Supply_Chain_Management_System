@extends('layouts.contentNavbarLayout')

@section('title', 'All Orders')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ri-shopping-cart-2-line" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">Total Orders</h5>
                    <h3>{{ $totalOrders }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ri-time-line" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">Pending Orders</h5>
                    <h3>{{ $pendingOrders }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ri-checkbox-circle-line" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">Completed Orders</h5>
                    <h3>{{ $completedOrders }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="ri-archive-2-line" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">Manage Inventory</h5>
                    <h3>{{ $productCount }}</h3>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-warning">Low: {{ $lowStockCount }}</span>
                        <span class="text-danger">Out: {{ $outOfStockCount }}</span>
                        <span class="text-success">UGX {{ number_format($totalValue, 0) }}</span>
                    </div>
                    <a href="{{ route('supplier.inventory') }}" class="btn btn-outline-primary btn-sm mt-2">Manage Inventory</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body d-flex flex-wrap gap-2">
                    <a href="{{ route('orders.create') }}" class="btn btn-primary">
                        <i class="ri-add-line"></i> Place New Order
                    </a>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                        <i class="ri-list-check"></i> View Orders
                    </a>
                    <a href="{{ route('supplier.inventory') }}" class="btn btn-outline-info">
                        <i class="ri-box-3-line"></i> Manage Inventory
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="btn btn-outline-success">
                        <i class="ri-store-line"></i> Browse Marketplace
                    </a>
                    <a href="{{ route('app-chat') }}" class="btn btn-outline-secondary">
                        <i class="ri-chat-3-line"></i> Customer Support
                    </a>
                </div>
            </div>
        </div>
    </div>
    <h2>Orders</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <h4>Orders You Placed</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Seller</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($placedOrders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->seller->name ?? '-' }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <ul class="mb-0">
                                    @foreach($order->items as $item)
                                    <li>{{ $item->product->name ?? 'Product' }} x {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                            @php
                                $isSeller = auth()->id() === ($order->seller->id ?? null);
                                $isBuyer = auth()->id() === ($order->buyer->id ?? null);
                            @endphp
                            {{-- Buyer actions --}}
                            @if($isBuyer)
                                @if($order->status === 'delivered')
                                    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="received">
                                        <button class="btn btn-success btn-sm" type="submit">Mark as Received</button>
                                    </form>
                                @endif
                                @if(in_array($order->status, ['pending', 'approved']))
                                    <form method="POST" action="{{ route('orders.cancel', $order) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
                                    </form>
                                @endif
                                @endif
                            </td>
                        </tr>
                @empty
                    <tr><td colspan="6">No orders placed.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h4>Orders You Received</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Buyer</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($receivedOrders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->buyer->name ?? '-' }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <ul class="mb-0">
                                @foreach($order->items as $item)
                                    <li>{{ $item->product->name ?? 'Product' }} x {{ $item->quantity }}</li>
                    @endforeach
                            </ul>
                        </td>
                        <td>
                            @php
                                $isSeller = auth()->id() === ($order->seller->id ?? null);
                                $isBuyer = auth()->id() === ($order->buyer->id ?? null);
                            @endphp
                            {{-- Seller actions --}}
                            @if($isSeller)
                                @if($order->status === 'pending')
                                    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-success btn-sm" type="submit">Approve</button>
                                    </form>
                                @elseif($order->status === 'approved')
                                    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="shipped">
                                        <button class="btn btn-info btn-sm" type="submit">Mark as Shipped</button>
                                    </form>
                                @elseif($order->status === 'shipped')
                                    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="delivered">
                                        <button class="btn btn-primary btn-sm" type="submit">Mark as Delivered</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No orders received.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection