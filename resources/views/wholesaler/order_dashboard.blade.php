@extends('layouts.contentNavbarLayout')

@section('title', 'Wholesaler Dashboard')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold py-3 mb-4">Wholesaler Dashboard</h4>

    {{-- Incoming Orders from Wholesalers --}}
    <div class="row mb-4">
        <div class="col-12">
            @if($orders->isEmpty())
                <div class="alert alert-info">No incoming orders from wholesalers at the moment.</div>
            @else
                <div class="card">
                    <h5 class="card-header">Incoming Orders from Wholesalers</h5>
                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Wholesaler</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Placed On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->buyer->name }}</td>
                                        <td>
                                            <span class="badge bg-label-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'approved' ? 'success' : 'secondary') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-{{ $order->payment_status === 'paid' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($order->payment_status ?? 'unpaid') }}
                                            </span>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($order->items as $item)
                                                    <li>{{ $item->product->name }} x {{ $item->quantity }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td><strong>UGX {{ number_format($order->total_amount, 0) }}</strong></td>
                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Actions</button>
                                                <ul class="dropdown-menu">
                                                    @if($order->status === 'pending')
                                                        <li>
                                                            <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="approved">
                                                                <button class="dropdown-item text-success">Approve</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if($order->status === 'approved')
                                                        <li>
                                                            <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="shipped">
                                                                <button class="dropdown-item text-primary">Mark as Shipped</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if($order->payment_status === 'pending_verification')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('payments.verify.form', $order) }}">Verify Payment</a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Place Order to Factory --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Place Order to Factory</h5>
                <div class="card-body">
                    <form action="{{ route('wholesaler.orders.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="seller_id" class="form-label">Select Factory</label>
                            <select name="seller_id" class="form-select" required>
                                @foreach($factories as $factory)
                                    <option value="{{ $factory->id }}">{{ $factory->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="order-items">
                            <div class="order-item mb-3">
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <select name="items[0][product_id]" class="form-select" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
