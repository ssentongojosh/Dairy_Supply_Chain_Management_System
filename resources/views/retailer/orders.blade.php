


@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2>Orders to Wholesaler (As retailer)</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order #</th>
                <th>To</th>
                <th>Status</th>
                <th>Total</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->seller->name ?? 'Unknown' }} ({{ ucfirst($order->seller->role ?? '') }})</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>UGX {{ number_format($order->total_amount, 0) }}</td>
                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        @if($order->status === 'pending')
                            <form action="{{ route('retailer.orders.cancel', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-danger btn-sm">Cancel Order</button>
                            </form>
                        @endif
                        {{-- Add more actions as needed --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
