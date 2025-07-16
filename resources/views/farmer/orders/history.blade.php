@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Order History</h2>
    @if($orders->isEmpty())
        <div class="alert alert-info">You have no orders yet.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ number_format($order->total_amount, 0) }} UGX</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection 