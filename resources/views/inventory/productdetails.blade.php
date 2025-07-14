@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container my-5">

    <h3 class="mb-4">Product: {{ $item->name }}</h3>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <h5 class="card-title">Current Stock</h5>
                    <p class="fw-bold">{{ $item->quantity }} {{ $item->unit }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Incoming Batches -->
    <h5 class="mt-4">📦 Incoming Batches</h5>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Quantity Produced</th>
                    <th>Manufacture Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batches as $batch)
                    <tr>
                        <td>{{ $batch->batch_code }}</td>
                        <td>{{ $batch->quantity_produced }} {{ $product->unit }}</td>
                        <td>{{ \Carbon\Carbon::parse($batch->production_date)->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Usage History -->
    <h5 class="mt-4">🧪 Usage History</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Quantity</th>
                    <th>Delivery Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usages as $item)
                    <tr>
                        <td>{{ $item->order_id ?? 'Manual Task' }}</td>
                        <td>{{ $item->quantity_used }} {{ $product->unit }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->used_on)->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
