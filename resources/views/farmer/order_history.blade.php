@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Order History</h2>

    @if($orders->isEmpty())
        <div class="alert alert-info">
            No orders received from suppliers yet.
        </div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#Order ID</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->buyer->name }} ({{ $order->buyer->email }})</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ ucfirst($order->payment_status) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('farmer.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('farmer.dashboard') }}" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
@endsection
