@extends('layouts.contentNavbarLayout')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-6 rounded shadow max-w-xl w-full text-center space-y-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Delivery Details</h2>

        <div class="text-left space-y-2 text-gray-700">
            <p><strong>Order ID:</strong> {{ $delivery->order_id }}</p>
            <p><strong>Item Name:</strong> {{ $delivery->item_name }}</p>
            <p><strong>Quantity:</strong> {{ $delivery->quantity }}</p>
            <p><strong>Description:</strong> {{ $delivery->notes }}</p>
            <p><strong>Delivery Date:</strong> {{ \Carbon\Carbon::parse($delivery->delivery_date)->toFormattedDateString() }}</p>
        </div>

        <div class="mt-6 text-lg font-semibold">
            @if ($delivery->status === 'pending' || $delivery->status === 'transit')
                <p class="text-yellow-600">⏳ Goods in transit — pending confirmation...</p>
            @elseif ($delivery->status === 'approved')
                <p class="text-green-600">✅ Delivery accepted — thank you for your supply!</p>
            @elseif ($delivery->status === 'rejected')
                <p class="text-red-600">❌ Delivery rejected — wait for further instructions.</p>
            @elseif ($delivery->status === 'terminated')
                <p class="text-gray-600">⚠️ Delivery was terminated by the sender.</p>
            @else
                <p>Status: {{ $delivery->status }}</p>
            @endif
        </div>

        <div class="flex justify-center gap-4 mt-6">
            @if($delivery->status === 'pending' || $delivery->status === 'transit')
                <form method="POST" action="{{ route('delivery.terminate', $delivery->id) }}">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        ❌ Cancel Delivery
                    </button>
                </form>
            @else
                <a href="{{ route('delivery.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    🔄 Exit & New Delivery
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($delivery->status === 'pending' || $delivery->status === 'transit')
<script>
    setTimeout(() => {
        location.reload();
    }, 5000); // auto refresh every 5 seconds
</script>
@endif
@endpush
